@extends('layouts.admin')

@section('title', 'MCQ Question Bank - HB Notes')
@section('page-title', 'MCQ Question Bank')

@section('content')
<!-- Reusable MCQ Navigation Bar -->
@include('admin.mcqs.partials.subnav')

<!-- Header Card with Search, Academic Hierarchy Filters & Actions -->
<div class="admin-card mb-4">
    <div class="admin-card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h3 class="admin-card-title mb-1">MCQ Question Bank ({{ $questions->total() }})</h3>
            <p class="text-muted small mb-0">Curated multiple choice questions categorized by syllabus chapters and difficulty.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Import History Button -->
            <a href="{{ route('admin.mcqs.import.history') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 px-3 py-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>Import History</span>
            </a>

            <!-- Prominent Import Questions CSV Button -->
            <a href="{{ route('admin.mcqs.import', ['class_id' => $classId, 'subject_id' => $subjectId, 'chapter_id' => $chapterId]) }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 shadow-sm px-3 py-2 fw-semibold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span>Import Questions (CSV)</span>
            </a>

            <!-- Single Question Add Button -->
            <a href="{{ route('admin.mcqs.create', ['class_id' => $classId, 'subject_id' => $subjectId, 'chapter_id' => $chapterId]) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3 py-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add Question</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="p-3 p-sm-4 border-bottom bg-light">
        <form method="GET" action="{{ route('admin.mcqs.index') }}" class="row g-2 align-items-center">
            <!-- Search Keyword -->
            <div class="col-12 col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="search"
                        class="form-control border-start-0"
                        placeholder="Search question, options..."
                        value="{{ $search }}"
                    >
                </div>
            </div>

            <!-- Class Filter -->
            <div class="col-6 col-md-2">
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}" {{ (string)$classId === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Subject Filter -->
            <div class="col-6 col-md-2">
                <select name="subject_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}" {{ (string)$subjectId === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->academicClass?->name ?? 'Class' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Chapter Filter -->
            <div class="col-6 col-md-2">
                <select name="chapter_id" class="form-select">
                    <option value="">All Chapters</option>
                    @foreach ($chapters as $ch)
                        <option value="{{ $ch->id }}" {{ (string)$chapterId === (string)$ch->id ? 'selected' : '' }}>
                            Ch {{ $ch->chapter_number }}: {{ $ch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Difficulty Filter -->
            <div class="col-6 col-md-1">
                <select name="difficulty" class="form-select">
                    <option value="">Level</option>
                    <option value="easy" {{ $difficulty === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ $difficulty === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ $difficulty === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-1">
                <select name="status" class="form-select">
                    <option value="">Status</option>
                    <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-6 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 px-2" title="Apply Filters">Filter</button>
                @if (!empty($search) || !empty($classId) || !empty($subjectId) || !empty($chapterId) || !empty($difficulty) || !empty($status))
                    <a href="{{ route('admin.mcqs.index') }}" class="btn btn-outline-secondary px-2" title="Reset Filters">✕</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Questions Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="text-nowrap" style="min-width: 320px;">Question &amp; Options</th>
                    <th class="text-nowrap">Chapter / Subject</th>
                    <th class="text-nowrap text-center">Correct Option</th>
                    <th class="text-nowrap">Difficulty</th>
                    <th class="text-nowrap">Status</th>
                    <th style="text-align: right;" class="text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($questions as $q)
                    <tr>
                        <!-- Question Text & Options Preview -->
                        <td>
                            <div class="fw-semibold text-dark mb-2" style="font-size: 0.95rem;">
                                {{ $q->question }}
                            </div>
                            <div class="row g-1 small">
                                <div class="col-6">
                                    <span class="badge {{ $q->correct_option === 'A' ? 'bg-success text-white' : 'bg-light text-dark border' }}">A</span>
                                    <span class="{{ $q->correct_option === 'A' ? 'fw-bold text-success' : 'text-muted' }}">{{ $q->option_a }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge {{ $q->correct_option === 'B' ? 'bg-success text-white' : 'bg-light text-dark border' }}">B</span>
                                    <span class="{{ $q->correct_option === 'B' ? 'fw-bold text-success' : 'text-muted' }}">{{ $q->option_b }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge {{ $q->correct_option === 'C' ? 'bg-success text-white' : 'bg-light text-dark border' }}">C</span>
                                    <span class="{{ $q->correct_option === 'C' ? 'fw-bold text-success' : 'text-muted' }}">{{ $q->option_c }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge {{ $q->correct_option === 'D' ? 'bg-success text-white' : 'bg-light text-dark border' }}">D</span>
                                    <span class="{{ $q->correct_option === 'D' ? 'fw-bold text-success' : 'text-muted' }}">{{ $q->option_d }}</span>
                                </div>
                            </div>
                            @if ($q->explanation)
                                <div class="text-muted small mt-2 fst-italic">
                                    <strong>Explanation:</strong> {{ $q->explanation }}
                                </div>
                            @endif
                        </td>

                        <!-- Hierarchy: Class » Subject » Chapter -->
                        <td class="text-nowrap">
                            <div class="small">
                                <span class="badge rounded-pill bg-light text-dark border">
                                    {{ $q->academic_class?->name ?? 'Class' }}
                                </span>
                                <span class="text-muted mx-1">&raquo;</span>
                                <span class="fw-medium text-dark">{{ $q->chapter?->subject?->name ?? 'Subject' }}</span>
                            </div>
                            <div class="text-muted small mt-1">
                                Ch {{ $q->chapter?->chapter_number ?? '—' }}: {{ $q->chapter?->name ?? 'Chapter' }}
                            </div>
                        </td>

                        <!-- Correct Option -->
                        <td class="text-nowrap text-center">
                            <span class="badge bg-success fs-6 px-3 py-1">
                                Option {{ $q->correct_option }}
                            </span>
                        </td>

                        <!-- Difficulty -->
                        <td class="text-nowrap">
                            @if ($q->difficulty === 'easy')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Easy</span>
                            @elseif ($q->difficulty === 'hard')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Hard</span>
                            @else
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1">Medium</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="text-nowrap">
                            <span class="status-pill {{ $q->status === 'published' ? 'active' : 'inactive' }}">
                                {{ ucfirst($q->status) }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td style="text-align: right;" class="text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('admin.mcqs.edit', $q) }}" class="btn btn-sm btn-outline-primary px-2 py-1" title="Edit Question">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>

                                <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" data-bs-toggle="modal" data-bs-target="#deleteMcqModal{{ $q->id }}" title="Delete Question">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteMcqModal{{ $q->id }}" tabindex="-1" aria-labelledby="deleteMcqModalLabel{{ $q->id }}" aria-hidden="true" style="text-align: left;">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger d-flex align-items-center gap-2" id="deleteMcqModalLabel{{ $q->id }}">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                                </svg>
                                                Delete MCQ Question
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="mb-2">Are you sure you want to permanently delete this question?</p>
                                            <div class="p-2 border rounded bg-light small mb-0">
                                                {{ Str::limit($q->question, 120) }}
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <form method="POST" action="{{ route('admin.mcqs.destroy', $q) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Permanently Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <p class="mb-3">No MCQ questions found matching your filter criteria.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.mcqs.import') }}" class="btn btn-success btn-sm">
                                    Import Questions (CSV)
                                </a>
                                <a href="{{ route('admin.mcqs.create') }}" class="btn btn-primary btn-sm">
                                    Add Question
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if ($questions->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">
                Showing {{ $questions->firstItem() }} to {{ $questions->lastItem() }} of {{ $questions->total() }} questions
            </span>
            <div>
                {{ $questions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection
