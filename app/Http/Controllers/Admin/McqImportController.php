<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\McqImportLog;
use App\Models\Subject;
use App\Services\McqCsvImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class McqImportController extends Controller
{
    public function __construct(
        protected McqCsvImportService $importService
    ) {}

    /**
     * Download the official CSV template for bulk MCQ questions.
     */
    public function template(): Response
    {
        $content = $this->importService->generateTemplate();

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="hb_notes_mcq_template.csv"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Display a paginated listing of past CSV import logs with audit status and statistics.
     */
    public function history(Request $request): View
    {
        $imports = McqImportLog::with(['chapter.subject.academicClass', 'user'])
            ->latest('id')
            ->paginate(15);

        return view('admin.mcqs.import-history', compact('imports'));
    }

    /**
     * Show the 4-step wizard page for CSV import.
     */
    public function create(Request $request): View
    {
        $selectedClassId = $request->input('class_id');
        $selectedSubjectId = $request->input('subject_id');
        $selectedChapterId = $request->input('chapter_id');

        $classes = AcademicClass::where('is_active', true)
            ->orderBy('class_number')
            ->get();

        $maxSizeKb = config('hbnotes.mcq_max_csv_size_kb', 5120);
        $maxRows = config('hbnotes.mcq_max_import_rows', 1000);

        return view('admin.mcqs.import', compact(
            'classes',
            'selectedClassId',
            'selectedSubjectId',
            'selectedChapterId',
            'maxSizeKb',
            'maxRows'
        ));
    }

    /**
     * Lookup active subjects for a selected class (AJAX endpoint).
     */
    public function lookupSubjects(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => ['required', 'integer', 'exists:academic_classes,id'],
        ]);

        $subjects = Subject::where('academic_class_id', $request->input('class_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json(['subjects' => $subjects]);
    }

    /**
     * Lookup active chapters for a selected subject (AJAX endpoint).
     */
    public function lookupChapters(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $chapters = Chapter::where('subject_id', $request->input('subject_id'))
            ->where('is_active', true)
            ->orderBy('chapter_number')
            ->get(['id', 'chapter_number', 'name']);

        return response()->json(['chapters' => $chapters]);
    }

    /**
     * Step 4 Upload & Preview: Validate file, parse rows, and show preview before import.
     */
    public function preview(Request $request): View|RedirectResponse
    {
        $maxSizeKb = config('hbnotes.mcq_max_csv_size_kb', 5120);

        $validated = $request->validate([
            'class_id' => ['required', 'integer', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'csv_file' => [
                'required',
                'file',
                'max:' . $maxSizeKb,
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['csv', 'txt'], true)) {
                        $fail('The uploaded file must be a standard CSV document (.csv).');
                    }
                },
            ],
        ], [
            'class_id.required' => 'Please select an academic class.',
            'subject_id.required' => 'Please select a curriculum subject.',
            'chapter_id.required' => 'Please select an educational chapter.',
            'csv_file.required' => 'A valid CSV document must be uploaded.',
            'csv_file.max' => 'The CSV document cannot exceed ' . round($maxSizeKb / 1024) . 'MB.',
        ]);

        // Server-side hierarchy and relationship verification
        $chapter = Chapter::with('subject.academicClass')->findOrFail($validated['chapter_id']);

        if ((int) $chapter->subject_id !== (int) $validated['subject_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the selected subject.']);
        }

        if ((int) $chapter->subject?->academic_class_id !== (int) $validated['class_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the selected academic class.']);
        }

        // Store CSV temporarily in private local storage
        $file = $request->file('csv_file');
        $importToken = Str::uuid()->toString();
        $storedFilename = $importToken . '.csv';
        $disk = config('hbnotes.pdf_storage_disk', 'local');
        $folder = config('hbnotes.csv_temp_storage_path', 'csv_imports');
        $storedPath = $file->storeAs($folder, $storedFilename, $disk);

        $fullPath = Storage::disk($disk)->path($storedPath);

        // Parse and validate rows
        $parseResult = $this->importService->parseAndValidate($fullPath, $chapter->id);

        if (!$parseResult['success']) {
            // Clean up temporary file
            Storage::disk($disk)->delete($storedPath);
            return back()->withInput()->withErrors(['csv_file' => $parseResult['error']]);
        }

        // Persist session log
        $importLog = McqImportLog::create([
            'import_token' => $importToken,
            'user_id' => Auth::id(),
            'chapter_id' => $chapter->id,
            'file_name' => $file->getClientOriginalName(),
            'stored_file_path' => $storedPath,
            'total_rows' => $parseResult['total_rows'],
            'duplicate_count' => $parseResult['duplicate_count'],
            'invalid_count' => $parseResult['invalid_count'],
            'preview_data' => $parseResult['preview_rows'],
            'error_data' => array_merge($parseResult['invalid_rows'], $parseResult['duplicate_rows']),
            'status' => 'preview',
        ]);

        return view('admin.mcqs.import-preview', compact('chapter', 'importLog', 'parseResult'));
    }

    /**
     * Step 5 Confirm Import: Commit valid rows in batches with database transactions.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'import_token' => ['required', 'string'],
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
        ]);

        $importLog = McqImportLog::where('import_token', $validated['import_token'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Idempotency check: prevent duplicate processing from repeated submits
        if ($importLog->status !== 'preview') {
            return redirect()->route('admin.mcqs.import.report', $importLog->id)
                ->with('status', 'This import has already been processed.');
        }

        $chapter = Chapter::with('subject.academicClass')->findOrFail($validated['chapter_id']);
        if ((int) $importLog->chapter_id !== (int) $chapter->id) {
            abort(403, 'Unauthorized chapter verification.');
        }

        $disk = config('hbnotes.pdf_storage_disk', 'local');
        if (!$importLog->stored_file_path || !Storage::disk($disk)->exists($importLog->stored_file_path)) {
            return redirect()->route('admin.mcqs.import')
                ->with('error', 'The temporary import file has expired or was removed. Please upload the CSV again.');
        }

        $fullPath = Storage::disk($disk)->path($importLog->stored_file_path);

        // Mark as processing
        $importLog->update(['status' => 'processing']);

        // Execute batch import
        $importResult = $this->importService->commitImport($fullPath, $chapter->id);

        // Update log with final counts and status
        $importLog->update([
            'imported_count' => $importResult['imported_count'],
            'duplicate_count' => $importResult['duplicate_count'],
            'invalid_count' => $importResult['invalid_count'],
            'failed_count' => $importResult['failed_count'],
            'status' => $importResult['status'],
            'error_data' => $importResult['errors'],
            'confirmed_at' => now(),
        ]);

        // Clean up temporary CSV file from storage
        Storage::disk($disk)->delete($importLog->stored_file_path);

        return redirect()->route('admin.mcqs.import.report', $importLog->id);
    }

    /**
     * Cancel an import during the preview stage and clean up the stored file.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'import_token' => ['required', 'string'],
        ]);

        $importLog = McqImportLog::where('import_token', $validated['import_token'])
            ->where('user_id', Auth::id())
            ->first();

        if ($importLog) {
            $disk = config('hbnotes.pdf_storage_disk', 'local');
            if ($importLog->stored_file_path && Storage::disk($disk)->exists($importLog->stored_file_path)) {
                Storage::disk($disk)->delete($importLog->stored_file_path);
            }
            $importLog->update(['status' => 'cancelled']);
        }

        return redirect()->route('admin.mcqs.index')
            ->with('status', 'CSV import was cancelled. No questions were saved.');
    }

    /**
     * Display the final import results report.
     */
    public function report(McqImportLog $import): View
    {
        $import->load(['chapter.subject.academicClass', 'user']);

        return view('admin.mcqs.import-report', compact('import'));
    }

    /**
     * Download the detailed row error report as a CSV with formula injection sanitization.
     */
    public function downloadErrors(McqImportLog $import): Response
    {
        $errors = $import->error_data ?? [];
        $content = $this->importService->generateErrorCsv($errors);

        $safeFilename = 'mcq_import_errors_' . $import->id . '.csv';

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $safeFilename . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
