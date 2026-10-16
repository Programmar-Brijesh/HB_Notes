@extends('layouts.admin')

@section('title', 'Edit MCQ Question - HB Notes')
@section('page-title', 'Edit MCQ Question')

@section('content')
<!-- Reusable MCQ Navigation Bar -->
@include('admin.mcqs.partials.subnav')

<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="admin-card-title mb-1">Edit MCQ Question</h3>
                    <span class="text-muted small">Update question wording, options, or answer key.</span>
                </div>
                <a href="{{ route('admin.mcqs.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back
                </a>
            </div>

            <div class="p-4">
                <form method="POST" action="{{ route('admin.mcqs.update', $mcq) }}">
                    @csrf
                    @method('PUT')

                    <!-- Hierarchy Selection: Class, Subject, Chapter -->
                    <div class="card p-3 bg-light border-0 mb-4 rounded-3">
                        <div class="fw-semibold text-dark small mb-3 text-uppercase" style="letter-spacing: 0.5px;">
                            Academic Curriculum Assignment
                        </div>

                        <div class="row g-3">
                            @php
                                $currentClassId = old('class_id', $mcq->chapter?->subject?->academic_class_id);
                                $currentSubjectId = old('subject_id', $mcq->chapter?->subject_id);
                                $currentChapterId = old('chapter_id', $mcq->chapter_id);
                            @endphp

                            <div class="col-12 col-md-4">
                                <label for="class_id" class="form-label small fw-semibold">Academic Class <span class="text-danger">*</span></label>
                                <select id="class_id" name="class_id" class="form-select @error('class_id') is-invalid @enderror">
                                    <option value="">Select Class...</option>
                                    @foreach ($classes as $c)
                                        <option value="{{ $c->id }}" {{ (string)$currentClassId === (string)$c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="subject_id" class="form-label small fw-semibold">Subject <span class="text-danger">*</span></label>
                                <select id="subject_id" name="subject_id" class="form-select @error('subject_id') is-invalid @enderror">
                                    <option value="">Select Subject...</option>
                                    @foreach ($classes as $c)
                                        @foreach ($c->subjects as $s)
                                            <option
                                                value="{{ $s->id }}"
                                                data-class-id="{{ $c->id }}"
                                                {{ (string)$currentSubjectId === (string)$s->id ? 'selected' : '' }}
                                            >
                                                {{ $s->name }} [{{ $c->name }}]
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="chapter_id" class="form-label small fw-semibold">Chapter <span class="text-danger">*</span></label>
                                <select id="chapter_id" name="chapter_id" class="form-select @error('chapter_id') is-invalid @enderror" required>
                                    <option value="">Select Chapter...</option>
                                    @foreach ($classes as $c)
                                        @foreach ($c->subjects as $s)
                                            @foreach ($s->chapters as $ch)
                                                <option
                                                    value="{{ $ch->id }}"
                                                    data-class-id="{{ $c->id }}"
                                                    data-subject-id="{{ $s->id }}"
                                                    {{ (string)$currentChapterId === (string)$ch->id ? 'selected' : '' }}
                                                >
                                                    Ch {{ $ch->chapter_number }}: {{ $ch->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('chapter_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Question Text -->
                    <div class="mb-4">
                        <label for="question" class="form-label small fw-semibold">Question Content <span class="text-danger">*</span></label>
                        <textarea
                            class="form-control @error('question') is-invalid @enderror"
                            id="question"
                            name="question"
                            rows="3"
                            required
                        >{{ old('question', $mcq->question) }}</textarea>
                        @error('question')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Four Options -->
                    <div class="card p-3 border rounded-3 mb-4 bg-light">
                        <div class="fw-semibold text-dark small mb-3 text-uppercase" style="letter-spacing: 0.5px;">
                            Answer Options (A, B, C, D)
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="option_a" class="form-label small fw-semibold">Option A <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('option_a') is-invalid @enderror" id="option_a" name="option_a" value="{{ old('option_a', $mcq->option_a) }}" required>
                                @error('option_a')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="option_b" class="form-label small fw-semibold">Option B <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('option_b') is-invalid @enderror" id="option_b" name="option_b" value="{{ old('option_b', $mcq->option_b) }}" required>
                                @error('option_b')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="option_c" class="form-label small fw-semibold">Option C <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('option_c') is-invalid @enderror" id="option_c" name="option_c" value="{{ old('option_c', $mcq->option_c) }}" required>
                                @error('option_c')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="option_d" class="form-label small fw-semibold">Option D <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('option_d') is-invalid @enderror" id="option_d" name="option_d" value="{{ old('option_d', $mcq->option_d) }}" required>
                                @error('option_d')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Correct Option, Difficulty, Status -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label for="correct_option" class="form-label small fw-semibold">Correct Answer <span class="text-danger">*</span></label>
                            <select id="correct_option" name="correct_option" class="form-select @error('correct_option') is-invalid @enderror" required>
                                <option value="A" {{ old('correct_option', $mcq->correct_option) === 'A' ? 'selected' : '' }}>Option A</option>
                                <option value="B" {{ old('correct_option', $mcq->correct_option) === 'B' ? 'selected' : '' }}>Option B</option>
                                <option value="C" {{ old('correct_option', $mcq->correct_option) === 'C' ? 'selected' : '' }}>Option C</option>
                                <option value="D" {{ old('correct_option', $mcq->correct_option) === 'D' ? 'selected' : '' }}>Option D</option>
                            </select>
                            @error('correct_option')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="difficulty" class="form-label small fw-semibold">Difficulty Level <span class="text-danger">*</span></label>
                            <select id="difficulty" name="difficulty" class="form-select @error('difficulty') is-invalid @enderror" required>
                                <option value="easy" {{ old('difficulty', $mcq->difficulty) === 'easy' ? 'selected' : '' }}>Easy</option>
                                <option value="medium" {{ old('difficulty', $mcq->difficulty) === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="hard" {{ old('difficulty', $mcq->difficulty) === 'hard' ? 'selected' : '' }}>Hard</option>
                            </select>
                            @error('difficulty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="status" class="form-label small fw-semibold">Publication Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="published" {{ old('status', $mcq->status) === 'published' ? 'selected' : '' }}>Published (Active)</option>
                                <option value="draft" {{ old('status', $mcq->status) === 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Explanation -->
                    <div class="mb-4">
                        <label for="explanation" class="form-label small fw-semibold">Explanation / Solution Note <span class="text-muted">(Optional)</span></label>
                        <textarea
                            class="form-control @error('explanation') is-invalid @enderror"
                            id="explanation"
                            name="explanation"
                            rows="2"
                        >{{ old('explanation', $mcq->explanation) }}</textarea>
                        @error('explanation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Actions -->
                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('admin.mcqs.index') }}" class="btn btn-outline-secondary px-4">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const classSelect = document.getElementById('class_id');
    const subjectSelect = document.getElementById('subject_id');
    const chapterSelect = document.getElementById('chapter_id');

    function filterSubjects() {
        const selectedClass = classSelect.value;
        const subjectOptions = subjectSelect.querySelectorAll('option');

        subjectOptions.forEach(opt => {
            if (!opt.value) return;
            const optClass = opt.getAttribute('data-class-id');
            if (!selectedClass || optClass === selectedClass) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
                if (opt.selected) subjectSelect.value = '';
            }
        });
        filterChapters();
    }

    function filterChapters() {
        const selectedClass = classSelect.value;
        const selectedSubject = subjectSelect.value;
        const chapterOptions = chapterSelect.querySelectorAll('option');

        chapterOptions.forEach(opt => {
            if (!opt.value) return;
            const optClass = opt.getAttribute('data-class-id');
            const optSubject = opt.getAttribute('data-subject-id');

            let match = true;
            if (selectedClass && optClass !== selectedClass) match = false;
            if (selectedSubject && optSubject !== selectedSubject) match = false;

            if (match) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
                if (opt.selected) chapterSelect.value = '';
            }
        });
    }

    classSelect.addEventListener('change', filterSubjects);
    subjectSelect.addEventListener('change', filterChapters);

    if (classSelect.value) filterSubjects();
});
</script>
@endsection
