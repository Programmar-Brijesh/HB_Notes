<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicClassController extends Controller
{
    /**
     * Display a listing of academic classes with search and pagination.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $query = AcademicClass::withCount('subjects');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('stream', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $classes = $query->orderBy('class_number')
            ->paginate(10)
            ->withQueryString();

        return view('admin.classes.index', compact('classes', 'search', 'status'));
    }

    /**
     * Show the form for creating a new academic class.
     */
    public function create(): View
    {
        return view('admin.classes.create');
    }

    /**
     * Store a newly created academic class.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:academic_classes,name'],
            'class_number' => ['required', 'integer', 'min:1', 'max:20', 'unique:academic_classes,class_number'],
            'stream' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $academicClass = AcademicClass::create($validated);

        return redirect()->route('admin.classes.index')
            ->with('status', "Academic class '{$academicClass->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified academic class.
     */
    public function edit(AcademicClass $class): View
    {
        return view('admin.classes.edit', compact('class'));
    }

    /**
     * Update the specified academic class.
     */
    public function update(Request $request, AcademicClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('academic_classes', 'name')->ignore($class->id),
            ],
            'class_number' => [
                'required',
                'integer',
                'min:1',
                'max:20',
                Rule::unique('academic_classes', 'class_number')->ignore($class->id),
            ],
            'stream' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $class->update($validated);

        return redirect()->route('admin.classes.index')
            ->with('status', "Academic class '{$class->name}' updated successfully.");
    }

    /**
     * Remove the specified academic class from storage with dependent integrity check.
     */
    public function destroy(AcademicClass $class): RedirectResponse
    {
        if ($class->subjects()->exists()) {
            $count = $class->subjects()->count();
            return redirect()->route('admin.classes.index')
                ->with('error', "Cannot delete '{$class->name}' because it contains {$count} subject(s). Please remove or reassign all subjects first.");
        }

        $name = $class->name;
        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('status', "Academic class '{$name}' deleted successfully.");
    }
}
