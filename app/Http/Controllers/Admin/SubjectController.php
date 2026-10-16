<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects with filters, search, and pagination.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $classId = $request->input('class_id');
        $status = $request->input('status');

        $query = Subject::with('academicClass')->withCount('chapters');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($classId)) {
            $query->where('academic_class_id', $classId);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $subjects = $query->orderBy('academic_class_id')
            ->orderBy('order_column')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $classes = AcademicClass::orderBy('class_number')->get();

        return view('admin.subjects.index', compact('subjects', 'classes', 'search', 'classId', 'status'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create(Request $request): View
    {
        $selectedClassId = $request->input('class_id');
        $classes = AcademicClass::where('is_active', true)->orderBy('class_number')->get();

        return view('admin.subjects.create', compact('classes', 'selectedClassId'));
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('subjects', 'name')->where('academic_class_id', $request->input('academic_class_id')),
            ],
            'code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['order_column'] = (int) ($request->input('order_column') ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        $subject = Subject::create($validated);

        return redirect()->route('admin.subjects.index', ['class_id' => $subject->academic_class_id])
            ->with('status', "Subject '{$subject->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject): View
    {
        $classes = AcademicClass::orderBy('class_number')->get();

        return view('admin.subjects.edit', compact('subject', 'classes'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $classId = $request->input('academic_class_id', $subject->academic_class_id);

        $validated = $request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('subjects', 'name')
                    ->where('academic_class_id', $classId)
                    ->ignore($subject->id),
            ],
            'code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'order_column' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['order_column'] = (int) ($request->input('order_column') ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index', ['class_id' => $subject->academic_class_id])
            ->with('status', "Subject '{$subject->name}' updated successfully.");
    }

    /**
     * Remove the specified subject from storage with dependent integrity check.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->chapters()->exists()) {
            $count = $subject->chapters()->count();
            return redirect()->route('admin.subjects.index')
                ->with('error', "Cannot delete subject '{$subject->name}' because it contains {$count} chapter(s). Please remove or reassign all chapters first.");
        }

        $name = $subject->name;
        $classId = $subject->academic_class_id;
        $subject->delete();

        return redirect()->route('admin.subjects.index', ['class_id' => $classId])
            ->with('status', "Subject '{$name}' deleted successfully.");
    }
}
