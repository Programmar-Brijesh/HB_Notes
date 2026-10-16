<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\McqQuestion;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class McqQuestionController extends Controller
{
    /**
     * Display a listing of MCQ questions with filters and search.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $chapterId = $request->input('chapter_id');
        $difficulty = $request->input('difficulty');
        $status = $request->input('status');

        $query = McqQuestion::with(['chapter.subject.academicClass']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('option_a', 'like', "%{$search}%")
                    ->orWhere('option_b', 'like', "%{$search}%")
                    ->orWhere('option_c', 'like', "%{$search}%")
                    ->orWhere('option_d', 'like', "%{$search}%");
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

        if (!empty($difficulty) && in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            $query->where('difficulty', $difficulty);
        }

        if (!empty($status) && in_array($status, ['published', 'draft'], true)) {
            $query->where('status', $status);
        }

        $questions = $query->orderBy('order_column')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $classes = AcademicClass::where('is_active', true)->orderBy('class_number')->get();

        $subjectsQuery = Subject::with('academicClass')->where('is_active', true)->orderBy('name');
        if (!empty($classId)) {
            $subjectsQuery->where('academic_class_id', $classId);
        }
        $subjects = $subjectsQuery->get();

        $chaptersQuery = Chapter::with('subject')->where('is_active', true)->orderBy('chapter_number');
        if (!empty($subjectId)) {
            $chaptersQuery->where('subject_id', $subjectId);
        } elseif (!empty($classId)) {
            $chaptersQuery->whereHas('subject', function ($q) use ($classId) {
                $q->where('academic_class_id', $classId);
            });
        }
        $chapters = $chaptersQuery->get();

        return view('admin.mcqs.index', compact(
            'questions',
            'classes',
            'subjects',
            'chapters',
            'search',
            'classId',
            'subjectId',
            'chapterId',
            'difficulty',
            'status'
        ));
    }

    /**
     * Show the form for creating a new MCQ question.
     */
    public function create(Request $request): View
    {
        $selectedClassId = $request->input('class_id');
        $selectedSubjectId = $request->input('subject_id');
        $selectedChapterId = $request->input('chapter_id');

        $classes = AcademicClass::with(['subjects.chapters'])->where('is_active', true)->orderBy('class_number')->get();

        return view('admin.mcqs.create', compact('classes', 'selectedClassId', 'selectedSubjectId', 'selectedChapterId'));
    }

    /**
     * Store a newly created MCQ question in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'class_id' => ['nullable', 'integer', 'exists:academic_classes,id'],
            'question' => ['required', 'string', 'max:5000'],
            'option_a' => ['required', 'string', 'max:2000'],
            'option_b' => ['required', 'string', 'max:2000'],
            'option_c' => ['required', 'string', 'max:2000'],
            'option_d' => ['required', 'string', 'max:2000'],
            'correct_option' => ['required', 'in:A,B,C,D,a,b,c,d'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'status' => ['required', 'in:draft,published'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        // Server-side hierarchy verification
        $chapter = Chapter::with('subject.academicClass')->findOrFail($validated['chapter_id']);
        if (!empty($validated['subject_id']) && (int) $chapter->subject_id !== (int) $validated['subject_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the selected subject.']);
        }
        if (!empty($validated['class_id']) && (int) $chapter->subject?->academic_class_id !== (int) $validated['class_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the selected academic class.']);
        }

        // Duplicate check within chapter
        $hash = McqQuestion::computeQuestionHash($validated['question']);
        if (McqQuestion::where('chapter_id', $chapter->id)->where('question_hash', $hash)->exists()) {
            return back()->withInput()->withErrors(['question' => 'A duplicate question already exists in this chapter.']);
        }

        $validated['correct_option'] = strtoupper($validated['correct_option']);
        $validated['question_hash'] = $hash;
        $validated['order_column'] = (int) ($validated['order_column'] ?? 0);
        $validated['is_active'] = true;

        $question = McqQuestion::create($validated);

        return redirect()->route('admin.mcqs.index', ['chapter_id' => $question->chapter_id])
            ->with('status', 'MCQ question created successfully.');
    }

    /**
     * Show the form for editing the specified MCQ question.
     */
    public function edit(McqQuestion $mcq): View
    {
        $mcq->load(['chapter.subject.academicClass']);
        $classes = AcademicClass::with(['subjects.chapters'])->where('is_active', true)->orderBy('class_number')->get();

        return view('admin.mcqs.edit', compact('mcq', 'classes'));
    }

    /**
     * Update the specified MCQ question in storage.
     */
    public function update(Request $request, McqQuestion $mcq): RedirectResponse
    {
        $validated = $request->validate([
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'class_id' => ['nullable', 'integer', 'exists:academic_classes,id'],
            'question' => ['required', 'string', 'max:5000'],
            'option_a' => ['required', 'string', 'max:2000'],
            'option_b' => ['required', 'string', 'max:2000'],
            'option_c' => ['required', 'string', 'max:2000'],
            'option_d' => ['required', 'string', 'max:2000'],
            'correct_option' => ['required', 'in:A,B,C,D,a,b,c,d'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'status' => ['required', 'in:draft,published'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        // Server-side hierarchy verification
        $chapter = Chapter::with('subject.academicClass')->findOrFail($validated['chapter_id']);
        if (!empty($validated['subject_id']) && (int) $chapter->subject_id !== (int) $validated['subject_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the selected subject.']);
        }
        if (!empty($validated['class_id']) && (int) $chapter->subject?->academic_class_id !== (int) $validated['class_id']) {
            return back()->withInput()->withErrors(['chapter_id' => 'The selected chapter does not belong to the selected academic class.']);
        }

        // Duplicate check within chapter excluding current question
        $hash = McqQuestion::computeQuestionHash($validated['question']);
        if (McqQuestion::where('chapter_id', $chapter->id)->where('question_hash', $hash)->where('id', '!=', $mcq->id)->exists()) {
            return back()->withInput()->withErrors(['question' => 'Another question with this same text already exists in this chapter.']);
        }

        $validated['correct_option'] = strtoupper($validated['correct_option']);
        $validated['question_hash'] = $hash;
        $validated['order_column'] = (int) ($validated['order_column'] ?? 0);

        $mcq->update($validated);

        return redirect()->route('admin.mcqs.index', ['chapter_id' => $mcq->chapter_id])
            ->with('status', 'MCQ question updated successfully.');
    }

    /**
     * Remove the specified MCQ question from storage.
     */
    public function destroy(McqQuestion $mcq): RedirectResponse
    {
        $chapterId = $mcq->chapter_id;
        $mcq->delete();

        return redirect()->route('admin.mcqs.index', ['chapter_id' => $chapterId])
            ->with('status', 'MCQ question deleted successfully.');
    }
}
