@extends('layouts.admin')

@section('title', 'Notes & PDFs Management - HB Notes')
@section('page-title', 'Curriculum Notes & PDF Documents')

@section('content')
<!-- Header Card with Search, Academic Hierarchy Filters & Add Note Action -->
<div class="admin-card mb-4">
    <div class="admin-card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h3 class="admin-card-title mb-1">Notes &amp; PDF Library ({{ $notes->total() }})</h3>
            <p class="text-muted small mb-0">Manage chapter-aligned PDF study material, status, and downloads.</p>
        </div>
        <div>
            <a href="{{ route('admin.notes.create', ['class_id' => $classId, 'subject_id' => $subjectId, 'chapter_id' => $chapterId]) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3 py-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Upload Note / PDF</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="p-3 p-sm-4 border-bottom bg-light">
        <form method="GET" action="{{ route('admin.notes.index') }}" class="row g-2 align-items-center">
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
                        placeholder="Search title, filename..."
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
            <div class="col-6 col-md-3">
                <select name="chapter_id" class="form-select">
                    <option value="">All Chapters</option>
                    @foreach ($chapters as $ch)
                        <option value="{{ $ch->id }}" {{ (string)$chapterId === (string)$ch->id ? 'selected' : '' }}>
                            Ch {{ $ch->chapter_number }}: {{ $ch->name }} ({{ $ch->subject?->name ?? 'Sub' }})
                        </option>
                    @endforeach
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
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 px-2" title="Apply Filters">Filter</button>
                @if (!empty($search) || !empty($classId) || !empty($subjectId) || !empty($chapterId) || !empty($status))
                    <a href="{{ route('admin.notes.index') }}" class="btn btn-outline-secondary px-2" title="Reset Filters">✕</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Notes & PDFs Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="text-nowrap" style="min-width: 220px;">Document Title</th>
                    <th class="text-nowrap">Academic Unit</th>
                    <th class="text-nowrap">File Details</th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap text-center">Downloads</th>
                    <th class="text-nowrap">Uploaded</th>
                    <th style="text-align: right;" class="text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notes as $note)
                    <tr>
                        <!-- Title & Thumbnail -->
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-2 rounded bg-light border text-danger d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                    </svg>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size: 0.925rem;">{{ $note->title }}</div>
                                    @if ($note->description)
                                        <div class="text-muted small text-truncate" style="max-width: 260px;">{{ $note->description }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Hierarchy: Class » Subject » Chapter -->
                        <td class="text-nowrap">
                            <div class="small">
                                <span class="badge rounded-pill bg-light text-dark border">
                                    {{ $note->academic_class?->name ?? 'Class' }}
                                </span>
                                <span class="text-muted mx-1">&raquo;</span>
                                <span class="fw-medium text-dark">{{ $note->chapter?->subject?->name ?? 'Subject' }}</span>
                            </div>
                            <div class="text-muted small mt-1">
                                Ch {{ $note->chapter?->chapter_number ?? '—' }}: {{ $note->chapter?->name ?? 'Chapter' }}
                            </div>
                        </td>

                        <!-- File Details -->
                        <td class="text-nowrap">
                            <div class="font-monospace small text-dark fw-semibold">
                                {{ $note->formatted_file_size }}
                            </div>
                            <div class="text-muted small text-truncate" style="max-width: 170px;" title="{{ $note->file_original_name }}">
                                {{ $note->file_original_name }}
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="text-nowrap">
                            <span class="status-pill {{ $note->status === 'published' ? 'active' : 'inactive' }}">
                                {{ ucfirst($note->status) }}
                            </span>
                        </td>

                        <!-- Downloads -->
                        <td class="text-nowrap text-center font-monospace small">
                            {{ number_format($note->download_count) }}
                        </td>

                        <!-- Uploaded At -->
                        <td class="text-nowrap small text-muted">
                            {{ $note->created_at->format('M d, Y') }}
                        </td>

                        <!-- Actions -->
                        <td style="text-align: right;" class="text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <!-- Secure Preview -->
                                <a href="{{ route('admin.notes.preview', $note) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Inline PDF Preview">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>

                                <!-- Secure Download -->
                                <a href="{{ route('admin.notes.download', $note) }}" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Download Document">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </a>

                                <!-- Edit -->
                                <a href="{{ route('admin.notes.edit', $note) }}" class="btn btn-sm btn-outline-primary px-2 py-1" title="Edit Note">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>

                                <!-- Delete Modal Trigger -->
                                <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" data-bs-toggle="modal" data-bs-target="#deleteNoteModal{{ $note->id }}" title="Delete Note">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteNoteModal{{ $note->id }}" tabindex="-1" aria-labelledby="deleteNoteModalLabel{{ $note->id }}" aria-hidden="true" style="text-align: left;">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger d-flex align-items-center gap-2" id="deleteNoteModalLabel{{ $note->id }}">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                                </svg>
                                                Delete Educational Note
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="mb-2">Are you sure you want to permanently delete <strong>"{{ $note->title }}"</strong>?</p>
                                            <div class="alert alert-warning small mb-0 py-2">
                                                This action will delete the note record from the database and permanently purge the stored PDF document (<code>{{ $note->file_original_name }}</code>) from storage.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <form method="POST" action="{{ route('admin.notes.destroy', $note) }}">
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
                        <td colspan="7" class="text-center py-5 text-muted">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="11" x2="12" y2="17"></line>
                                <line x1="9" y1="14" x2="15" y2="14"></line>
                            </svg>
                            <p class="mb-2">No educational notes or PDFs found matching your criteria.</p>
                            <a href="{{ route('admin.notes.create') }}" class="btn btn-primary btn-sm">Upload New Note</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if ($notes->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">
                Showing {{ $notes->firstItem() }} to {{ $notes->lastItem() }} of {{ $notes->total() }} notes
            </span>
            <div>
                {{ $notes->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection
