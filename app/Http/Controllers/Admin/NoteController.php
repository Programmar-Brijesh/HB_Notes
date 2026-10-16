<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Note;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NoteController extends Controller
{
    /**
     * Display a paginated listing of notes with search, class, subject, chapter, and status filters.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $chapterId = $request->input('chapter_id');
        $status = $request->input('status');

        $query = Note::with(['chapter.subject.academicClass']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('file_original_name', 'like', "%{$search}%");
            });
        }

        if (!empty($chapterId)) {
            $query->where('chapter_id', $chapterId);
        } elseif (!empty($subjectId)) {
            $query->whereHas('chapter', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        } elseif (!empty($classId)) {
            $query->whereHas('chapter.subject', function ($q) use ($classId) {
                $q->where('academic_class_id', $classId);
            });
        }

        if ($status !== null && in_array($status, ['published', 'draft'], true)) {
            $query->where('status', $status);
        }

        $notes = $query->orderBy('order_column')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $classes = AcademicClass::orderBy('class_number')->get();

        $subjectsQuery = Subject::with('academicClass')->orderBy('name');
        if (!empty($classId)) {
            $subjectsQuery->where('academic_class_id', $classId);
        }
        $subjects = $subjectsQuery->get();

        $chaptersQuery = Chapter::with('subject')->orderBy('chapter_number');
        if (!empty($subjectId)) {
            $chaptersQuery->where('subject_id', $subjectId);
        } elseif (!empty($classId)) {
            $chaptersQuery->whereHas('subject', function ($q) use ($classId) {
                $q->where('academic_class_id', $classId);
            });
        }
        $chapters = $chaptersQuery->get();

        return view('admin.notes.index', compact(
            'notes',
            'classes',
            'subjects',
            'chapters',
            'search',
            'classId',
            'subjectId',
            'chapterId',
            'status'
        ));
    }

    /**
     * Show the form for creating a new educational note with PDF upload.
     */
    public function create(Request $request): View
    {
        $selectedClassId = $request->input('class_id');
        $selectedSubjectId = $request->input('subject_id');
        $selectedChapterId = $request->input('chapter_id');

        $classes = AcademicClass::with(['subjects' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }, 'subjects.chapters' => function ($q) {
            $q->where('is_active', true)->orderBy('chapter_number');
        }])->where('is_active', true)->orderBy('class_number')->get();

        return view('admin.notes.create', compact(
            'classes',
            'selectedClassId',
            'selectedSubjectId',
            'selectedChapterId'
        ));
    }

    /**
     * Store a newly created note in storage with secure server-side file handling.
     */
    public function store(Request $request): RedirectResponse
    {
        $maxPdfSize = config('hbnotes.max_pdf_size_kb', 51200);
        $maxThumbSize = config('hbnotes.max_thumbnail_size_kb', 5120);

        $validated = $request->validate([
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'class_id' => ['nullable', 'integer', 'exists:academic_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:draft,published'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'pdf_file' => [
                'required',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:' . $maxPdfSize,
            ],
            'thumbnail' => [
                'nullable',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:' . $maxThumbSize,
            ],
        ], [
            'pdf_file.required' => 'A valid PDF document is required for the note.',
            'pdf_file.mimes' => 'The uploaded file must be a genuine PDF document.',
            'pdf_file.mimetypes' => 'The uploaded file must be a valid PDF document with MIME type application/pdf.',
            'pdf_file.max' => 'The PDF document cannot exceed ' . round($maxPdfSize / 1024) . 'MB.',
            'thumbnail.image' => 'The thumbnail must be a valid image file (JPEG, PNG, or WebP).',
            'thumbnail.max' => 'The thumbnail image cannot exceed ' . round($maxThumbSize / 1024) . 'MB.',
        ]);

        // Server-side hierarchy and relationship verification
        $chapter = Chapter::with('subject.academicClass')->findOrFail($validated['chapter_id']);

        if (!empty($validated['subject_id']) && (int) $chapter->subject_id !== (int) $validated['subject_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the chosen subject.']);
        }

        if (!empty($validated['class_id']) && (int) $chapter->subject?->academic_class_id !== (int) $validated['class_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the chosen academic class.']);
        }

        $disk = config('hbnotes.pdf_storage_disk', 'local');
        $pdfFolder = config('hbnotes.pdf_storage_path', 'notes/pdf');
        $thumbFolder = config('hbnotes.thumbnail_storage_path', 'notes/thumbnails');

        // Store PDF with safe random storage name
        $pdfFile = $request->file('pdf_file');
        $safePdfFilename = Str::random(40) . '.pdf';
        $storedPdfPath = $pdfFile->storeAs($pdfFolder, $safePdfFilename, $disk);

        $storedThumbPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbFile = $request->file('thumbnail');
            $safeThumbFilename = Str::random(40) . '.' . $thumbFile->getClientOriginalExtension();
            $storedThumbPath = $thumbFile->storeAs($thumbFolder, $safeThumbFilename, $disk);
        }

        try {
            $note = Note::create([
                'chapter_id' => $chapter->id,
                'title' => $validated['title'],
                'slug' => !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']),
                'description' => $validated['description'] ?? null,
                'pdf_path' => $storedPdfPath,
                'file_original_name' => $pdfFile->getClientOriginalName(),
                'file_size' => $pdfFile->getSize(),
                'mime_type' => 'application/pdf',
                'status' => $validated['status'],
                'thumbnail_path' => $storedThumbPath,
                'is_active' => $request->boolean('is_active', true),
                'order_column' => (int) ($validated['order_column'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            // Clean up newly uploaded files on creation failure
            if ($storedPdfPath && Storage::disk($disk)->exists($storedPdfPath)) {
                Storage::disk($disk)->delete($storedPdfPath);
            }
            if ($storedThumbPath && Storage::disk($disk)->exists($storedThumbPath)) {
                Storage::disk($disk)->delete($storedThumbPath);
            }
            throw $e;
        }

        return redirect()->route('admin.notes.index', ['chapter_id' => $note->chapter_id])
            ->with('status', "Note '{$note->title}' was uploaded and published successfully.");
    }

    /**
     * Show the form for editing the specified note.
     */
    public function edit(Note $note): View
    {
        $note->load(['chapter.subject.academicClass']);

        $classes = AcademicClass::with(['subjects' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }, 'subjects.chapters' => function ($q) {
            $q->where('is_active', true)->orderBy('chapter_number');
        }])->where('is_active', true)->orderBy('class_number')->get();

        return view('admin.notes.edit', compact('note', 'classes'));
    }

    /**
     * Update the specified note in storage with atomic file replacement.
     */
    public function update(Request $request, Note $note): RedirectResponse
    {
        $maxPdfSize = config('hbnotes.max_pdf_size_kb', 51200);
        $maxThumbSize = config('hbnotes.max_thumbnail_size_kb', 5120);

        $validated = $request->validate([
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'class_id' => ['nullable', 'integer', 'exists:academic_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:draft,published'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'pdf_file' => [
                'nullable',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:' . $maxPdfSize,
            ],
            'thumbnail' => [
                'nullable',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:' . $maxThumbSize,
            ],
        ], [
            'pdf_file.mimes' => 'The uploaded file must be a genuine PDF document.',
            'pdf_file.mimetypes' => 'The uploaded file must be a valid PDF document with MIME type application/pdf.',
            'pdf_file.max' => 'The PDF document cannot exceed ' . round($maxPdfSize / 1024) . 'MB.',
            'thumbnail.image' => 'The thumbnail must be a valid image file (JPEG, PNG, or WebP).',
            'thumbnail.max' => 'The thumbnail image cannot exceed ' . round($maxThumbSize / 1024) . 'MB.',
        ]);

        // Server-side hierarchy and relationship verification
        $chapter = Chapter::with('subject.academicClass')->findOrFail($validated['chapter_id']);

        if (!empty($validated['subject_id']) && (int) $chapter->subject_id !== (int) $validated['subject_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the chosen subject.']);
        }

        if (!empty($validated['class_id']) && (int) $chapter->subject?->academic_class_id !== (int) $validated['class_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the chosen academic class.']);
        }

        $disk = config('hbnotes.pdf_storage_disk', 'local');
        $oldPdfPath = $note->pdf_path;
        $newPdfStored = null;

        $updateData = [
            'chapter_id' => $chapter->id,
            'title' => $validated['title'],
            'slug' => !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'is_active' => $request->boolean('is_active', true),
            'order_column' => (int) ($validated['order_column'] ?? 0),
        ];

        // Process PDF replacement if a new file is uploaded
        if ($request->hasFile('pdf_file')) {
            $pdfFile = $request->file('pdf_file');
            $safePdfFilename = Str::random(40) . '.pdf';
            $newPdfStored = $pdfFile->storeAs(config('hbnotes.pdf_storage_path', 'notes/pdf'), $safePdfFilename, $disk);

            $updateData['pdf_path'] = $newPdfStored;
            $updateData['file_original_name'] = $pdfFile->getClientOriginalName();
            $updateData['file_size'] = $pdfFile->getSize();
            $updateData['mime_type'] = 'application/pdf';
        }

        // Process Thumbnail replacement if a new image is uploaded
        $oldThumbPath = $note->thumbnail_path;
        $newThumbStored = null;
        if ($request->hasFile('thumbnail')) {
            $thumbFile = $request->file('thumbnail');
            $safeThumbFilename = Str::random(40) . '.' . $thumbFile->getClientOriginalExtension();
            $newThumbStored = $thumbFile->storeAs(config('hbnotes.thumbnail_storage_path', 'notes/thumbnails'), $safeThumbFilename, $disk);
            $updateData['thumbnail_path'] = $newThumbStored;
        }

        DB::beginTransaction();
        try {
            $note->update($updateData);
            DB::commit();

            // Delete old files only AFTER the database commit succeeds
            if ($newPdfStored && $oldPdfPath && $oldPdfPath !== $newPdfStored) {
                if (Storage::disk($disk)->exists($oldPdfPath)) {
                    Storage::disk($disk)->delete($oldPdfPath);
                }
            }

            if ($newThumbStored && $oldThumbPath && $oldThumbPath !== $newThumbStored) {
                if (Storage::disk($disk)->exists($oldThumbPath)) {
                    Storage::disk($disk)->delete($oldThumbPath);
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            // If update failed, clean up newly stored files so no broken/orphan files remain
            if ($newPdfStored && Storage::disk($disk)->exists($newPdfStored)) {
                Storage::disk($disk)->delete($newPdfStored);
            }
            if ($newThumbStored && Storage::disk($disk)->exists($newThumbStored)) {
                Storage::disk($disk)->delete($newThumbStored);
            }

            throw $e;
        }

        return redirect()->route('admin.notes.index', ['chapter_id' => $note->chapter_id])
            ->with('status', "Note '{$note->title}' updated successfully.");
    }

    /**
     * Remove the specified note and clean up its stored files.
     */
    public function destroy(Note $note): RedirectResponse
    {
        $disk = config('hbnotes.pdf_storage_disk', 'local');
        $title = $note->title;
        $chapterId = $note->chapter_id;
        $pdfPath = $note->pdf_path;
        $thumbPath = $note->thumbnail_path;

        $note->delete();

        if ($pdfPath && Storage::disk($disk)->exists($pdfPath)) {
            Storage::disk($disk)->delete($pdfPath);
        }

        if ($thumbPath && Storage::disk($disk)->exists($thumbPath)) {
            Storage::disk($disk)->delete($thumbPath);
        }

        return redirect()->route('admin.notes.index', ['chapter_id' => $chapterId])
            ->with('status', "Note '{$title}' and its associated stored documents were deleted successfully.");
    }

    /**
     * Controlled secure download endpoint for authenticated administrators.
     */
    public function download(Note $note)
    {
        $disk = config('hbnotes.pdf_storage_disk', 'local');

        if (!Storage::disk($disk)->exists($note->pdf_path)) {
            abort(404, 'The requested note file was not found in storage.');
        }

        $note->increment('download_count');

        $downloadFilename = $note->file_original_name ?: ($note->slug . '.pdf');

        return Storage::disk($disk)->download($note->pdf_path, $downloadFilename);
    }

    /**
     * Controlled secure inline browser preview for authenticated administrators.
     */
    public function preview(Note $note): BinaryFileResponse
    {
        $disk = config('hbnotes.pdf_storage_disk', 'local');

        if (!Storage::disk($disk)->exists($note->pdf_path)) {
            abort(404, 'The requested note file was not found in storage.');
        }

        $fullPath = Storage::disk($disk)->path($note->pdf_path);
        $filename = $note->file_original_name ?: ($note->slug . '.pdf');

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }

    /**
     * Controlled secure thumbnail preview for authenticated administrators.
     */
    public function thumbnail(Note $note): BinaryFileResponse
    {
        if (!$note->thumbnail_path) {
            abort(404, 'No thumbnail uploaded for this note.');
        }

        $disk = config('hbnotes.pdf_storage_disk', 'local');

        if (!Storage::disk($disk)->exists($note->thumbnail_path)) {
            abort(404, 'The thumbnail file was not found in storage.');
        }

        $fullPath = Storage::disk($disk)->path($note->thumbnail_path);

        return response()->file($fullPath);
    }
}
