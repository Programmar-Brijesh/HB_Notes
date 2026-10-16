<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChapterController extends Controller
{
    /**
     * Display a listing of chapters with class, subject filters, search, and pagination.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $status = $request->input('status');

        $query = Chapter::with(['subject.academicClass']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('chapter_number', $search);
            });
        }

        if (!empty($subjectId)) {
            $query->where('subject_id', $subjectId);
        } elseif (!empty($classId)) {
            $query->whereHas('subject', function ($q) use ($classId) {
                $q->where('academic_class_id', $classId);
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $chapters = $query->orderBy('subject_id')
            ->orderBy('chapter_number')
            ->paginate(15)
            ->withQueryString();

        $classes = AcademicClass::orderBy('class_number')->get();
        $subjectsQuery = Subject::with('academicClass')->orderBy('name');
        if (!empty($classId)) {
            $subjectsQuery->where('academic_class_id', $classId);
        }
        $subjects = $subjectsQuery->get();

        return view('admin.chapters.index', compact('chapters', 'classes', 'subjects', 'search', 'classId', 'subjectId', 'status'));
    }

    /**
     * Show the form for creating a new chapter.
     */
    public function create(Request $request): View
    {
        $selectedSubjectId = $request->input('subject_id');
        $selectedClassId = $request->input('class_id');

        $classes = AcademicClass::with('subjects')->where('is_active', true)->orderBy('class_number')->get();
        $subjects = Subject::with('academicClass')->where('is_active', true)->orderBy('name')->get();

        return view('admin.chapters.create', compact('classes', 'subjects', 'selectedClassId', 'selectedSubjectId'));
    }

    /**
     * Store a newly created chapter in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'chapter_number' => [
                'required',
                'integer',
                'min:1',
                'max:150',
                Rule::unique('chapters', 'chapter_number')->where('subject_id', $request->input('subject_id')),
            ],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['order_column'] = (int) ($request->input('order_column') ?? $validated['chapter_number']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $chapter = Chapter::create($validated);

        return redirect()->route('admin.chapters.index', ['subject_id' => $chapter->subject_id])
            ->with('status', "Chapter '{$chapter->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified chapter.
     */
    public function edit(Chapter $chapter): View
    {
        $subjects = Subject::with('academicClass')->orderBy('name')->get();

        return view('admin.chapters.edit', compact('chapter', 'subjects'));
    }

    /**
     * Update the specified chapter in storage.
     */
    public function update(Request $request, Chapter $chapter): RedirectResponse
    {
        $subjectId = $request->input('subject_id', $chapter->subject_id);

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'chapter_number' => [
                'required',
                'integer',
                'min:1',
                'max:150',
                Rule::unique('chapters', 'chapter_number')
                    ->where('subject_id', $subjectId)
                    ->ignore($chapter->id),
            ],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['order_column'] = (int) ($request->input('order_column') ?? $validated['chapter_number']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $chapter->update($validated);

        return redirect()->route('admin.chapters.index', ['subject_id' => $chapter->subject_id])
            ->with('status', "Chapter '{$chapter->name}' updated successfully.");
    }

    /**
     * Remove the specified chapter from storage.
     */
    public function destroy(Chapter $chapter): RedirectResponse
    {
        if ($chapter->notes()->exists()) {
            return back()->with('error', "Cannot delete chapter '{$chapter->name}' because it contains uploaded notes/PDFs. Delete or reassign the notes first.");
        }

        if ($chapter->mcqQuestions()->exists()) {
            return back()->with('error', "Cannot delete chapter '{$chapter->name}' because it contains MCQ questions. Delete or reassign the questions first.");
        }

        $name = $chapter->name;
        $subjectId = $chapter->subject_id;
        $chapter->delete();

        return redirect()->route('admin.chapters.index', ['subject_id' => $subjectId])
            ->with('status', "Chapter '{$name}' deleted successfully.");
    }
}
