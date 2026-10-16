@extends('layouts.admin')

@section('title', 'Manage Chapters - HB Notes')
@section('page-title', 'Curriculum Chapters')

@section('content')
<!-- Header Card with Search, Subject Filters & Add Chapter Action -->
<div class="admin-card mb-4">
    <div class="admin-card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h3 class="admin-card-title mb-1">Academic Chapters ({{ $chapters->total() }})</h3>
            <p class="text-muted small mb-0">Manage chapter units and topics across all subjects and classes.</p>
        </div>
        <div>
            <a href="{{ route('admin.chapters.create', ['subject_id' => $subjectId, 'class_id' => $classId]) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3 py-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add Chapter</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="p-3 p-sm-4 border-bottom bg-light">
        <form method="GET" action="{{ route('admin.chapters.index') }}" class="row g-2 align-items-center">
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
                        placeholder="Search chapter or number..."
                        value="{{ $search }}"
                    >
                </div>
            </div>

            <!-- Class Filter -->
            <div class="col-6 col-md-3">
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Academic Classes</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}" {{ (string)$classId === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Subject Filter -->
            <div class="col-6 col-md-3">
                <select name="subject_id" class="form-select">
                    <option value="">All Subjects</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}" {{ (string)$subjectId === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->academicClass?->name ?? 'Class' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-6 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 px-2" title="Apply Filters">Filter</button>
                @if (!empty($search) || !empty($classId) || !empty($subjectId) || $status !== null && $status !== '')
                    <a href="{{ route('admin.chapters.index') }}" class="btn btn-outline-secondary px-2" title="Reset Filters">✕</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Chapters Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="text-nowrap" style="width: 80px;">Unit #</th>
                    <th class="text-nowrap">Chapter Title</th>
                    <th class="text-nowrap">Subject</th>
                    <th class="text-nowrap">Academic Class</th>
                    <th>Status</th>
                    <th style="text-align: right;" class="text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($chapters as $chapter)
                    <tr>
                        <td class="text-nowrap">
                            <span class="badge rounded-pill bg-light text-primary border border-primary-subtle font-monospace px-3 py-1">
                                Ch. {{ $chapter->chapter_number }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <div class="fw-bold" style="color: var(--color-navy-900); font-size: 0.95rem;">
                                {{ $chapter->name }}
                            </div>
                            <div class="text-muted small" style="max-width: 320px; white-space: normal;">
                                {{ $chapter->description ?: 'No chapter description provided' }}
                            </div>
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.chapters.index', ['subject_id' => $chapter->subject_id]) }}" class="badge rounded-pill bg-light text-dark border px-3 py-1 text-decoration-none">
                                {{ $chapter->subject?->name ?? 'Unassigned' }}
                            </a>
                        </td>
                        <td class="text-nowrap">
                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                {{ $chapter->subject?->academicClass?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <span class="status-pill {{ $chapter->is_active ? 'active' : 'inactive' }}">
                                {{ $chapter->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td style="text-align: right;" class="text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('admin.chapters.edit', $chapter) }}" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Edit Chapter">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.chapters.destroy', $chapter) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete Chapter {{ $chapter->chapter_number }}: {{ $chapter->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete Chapter">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            No chapters found matching your query.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($chapters->hasPages())
        <div class="p-3 border-top d-flex justify-content-center">
            {{ $chapters->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
