@extends('layouts.admin')

@section('title', 'Admin Dashboard - HB Notes')
@section('page-title', 'Overview & Academic Structure')

@section('content')
<!-- Database-Backed Metric Cards -->
<div class="row g-2 g-sm-3 mb-4">
    <!-- Total Classes -->
    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.classes.index') }}" class="text-decoration-none">
            <div class="metric-card h-100">
                <div class="metric-header">
                    <span class="metric-title">Academic Classes</span>
                    <div class="metric-icon-box" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="metric-value">{{ $totalClassesCount }}</div>
                <div class="metric-subtext">Classes 9, 10, 11 &amp; 12</div>
            </div>
        </a>
    </div>

    <!-- Total Subjects -->
    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.subjects.index') }}" class="text-decoration-none">
            <div class="metric-card h-100">
                <div class="metric-header">
                    <span class="metric-title">Total Subjects</span>
                    <div class="metric-icon-box" style="background: rgba(14, 165, 233, 0.1); color: #0284C7;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="metric-value">{{ $totalSubjectsCount }}</div>
                <div class="metric-subtext">Across all academic tiers</div>
            </div>
        </a>
    </div>

    <!-- Total Chapters -->
    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.chapters.index') }}" class="text-decoration-none">
            <div class="metric-card h-100">
                <div class="metric-header">
                    <span class="metric-title">Curated Chapters</span>
                    <div class="metric-icon-box" style="background: rgba(139, 92, 246, 0.1); color: #7C3AED;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </div>
                </div>
                <div class="metric-value">{{ $totalChaptersCount }}</div>
                <div class="metric-subtext">Syllabus-aligned units</div>
            </div>
        </a>
    </div>

    <!-- Total Notes & PDFs -->
    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.notes.index') }}" class="text-decoration-none">
            <div class="metric-card h-100">
                <div class="metric-header">
                    <span class="metric-title">Notes &amp; PDFs</span>
                    <div class="metric-icon-box" style="background: rgba(236, 72, 153, 0.1); color: #DB2777;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                </div>
                <div class="metric-value">{{ $totalNotesCount }}</div>
                <div class="metric-subtext">{{ $publishedNotesCount }} published online</div>
            </div>
        </a>
    </div>

    <!-- MCQ Question Bank -->
    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.mcqs.index') }}" class="text-decoration-none">
            <div class="metric-card h-100">
                <div class="metric-header">
                    <span class="metric-title">MCQ Bank</span>
                    <div class="metric-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #059669;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                </div>
                <div class="metric-value">{{ $totalMcqsCount }}</div>
                <div class="metric-subtext">{{ $publishedMcqsCount }} published active</div>
            </div>
        </a>
    </div>

    <!-- Active Administrators -->
    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.administrators.index') }}" class="text-decoration-none">
            <div class="metric-card h-100">
                <div class="metric-header">
                    <span class="metric-title">Active Admins</span>
                    <div class="metric-icon-box" style="background: rgba(59, 130, 246, 0.1); color: #2563EB;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                        </svg>
                    </div>
                </div>
                <div class="metric-value">{{ $activeAdminCount }}</div>
                <div class="metric-subtext">Active team members</div>
            </div>
        </a>
    </div>

    <!-- Pending Invites -->
    <div class="col-12 col-md-4 col-xl">
        <a href="{{ route('admin.administrators.index') }}" class="text-decoration-none">
            <div class="metric-card h-100">
                <div class="metric-header">
                    <span class="metric-title">Pending Invites</span>
                    <div class="metric-icon-box" style="background: rgba(245, 158, 11, 0.1); color: #D97706;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 14 14"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="metric-value">{{ $pendingInvitationCount }}</div>
                <div class="metric-subtext">Awaiting acceptance</div>
            </div>
        </a>
    </div>
</div>

<!-- Quick Actions Action Bar -->
<div class="admin-banner-card mb-4 p-3 p-sm-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <h2 class="admin-banner-heading mb-1">Academic Structure Management</h2>
            <p class="admin-banner-desc mb-0">
                Configure educational classes, organize subject curricula, and structure chapter units for secondary and senior secondary students.
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.classes.create') }}" class="btn btn-light btn-sm fw-semibold d-inline-flex align-items-center gap-1 shadow-sm py-2 px-3">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add Class</span>
            </a>
            <a href="{{ route('admin.subjects.create') }}" class="btn btn-outline-light btn-sm fw-semibold d-inline-flex align-items-center gap-1 py-2 px-3">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add Subject</span>
            </a>
            <a href="{{ route('admin.chapters.create') }}" class="btn btn-outline-light btn-sm fw-semibold d-inline-flex align-items-center gap-1 py-2 px-3">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add Chapter</span>
            </a>
            <a href="{{ route('admin.notes.create') }}" class="btn btn-warning btn-sm fw-semibold d-inline-flex align-items-center gap-1 py-2 px-3 text-dark shadow-sm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="11" x2="12" y2="17"></line>
                    <line x1="9" y1="14" x2="15" y2="14"></line>
                </svg>
                <span>Upload Note</span>
            </a>
            <a href="{{ route('admin.mcqs.import') }}" class="btn btn-success btn-sm fw-semibold d-inline-flex align-items-center gap-1 py-2 px-3 shadow-sm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span>Import MCQs (CSV)</span>
            </a>
        </div>
    </div>
</div>

<!-- Database-Backed Academic Classes Overview Grid -->
<div class="admin-card mb-4">
    <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
        <div>
            <h3 class="admin-card-title mb-0">Academic Curriculums (Classes 9–12)</h3>
            <span class="text-muted small">Curriculum Hierarchy &amp; Management</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.classes.index') }}" class="btn btn-sm btn-outline-primary fw-semibold px-3">
                Manage All Classes &rarr;
            </a>
        </div>
    </div>
    <div class="p-3 p-sm-4">
        <div class="row g-3">
            @forelse ($classes as $item)
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="class-card h-100 d-flex flex-column justify-content-between p-3 p-sm-4">
                        <div>
                            <div class="class-card-header d-flex align-items-center justify-content-between mb-2">
                                <h4 class="class-card-title mb-0">{{ $item->name }}</h4>
                                <span class="status-pill {{ $item->is_active ? 'active' : 'inactive' }}">
                                    {{ $item->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </div>
                            <div class="text-primary small fw-semibold mb-1">{{ $item->stream ?? 'General Curriculum' }}</div>
                            <p class="class-card-desc mb-3">{{ $item->description ?: 'Academic syllabus and exam preparatory materials.' }}</p>

                            <!-- Academic Counter Pills -->
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    {{ $item->subjects_count }} Subjects
                                </span>
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                    {{ $item->chapters_count }} Chapters
                                </span>
                            </div>
                        </div>
                        <div class="pt-2 d-flex flex-column gap-2">
                            <a href="{{ route('admin.subjects.index', ['class_id' => $item->id]) }}" class="btn btn-primary btn-sm w-100 py-2 d-inline-flex align-items-center justify-content-center gap-1">
                                <span>Browse Subjects</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <a href="{{ route('admin.subjects.create', ['class_id' => $item->id]) }}" class="btn btn-outline-secondary btn-sm w-50 py-1" style="font-size: 0.775rem;">
                                    + Subject
                                </a>
                                <a href="{{ route('admin.classes.edit', $item) }}" class="btn btn-outline-secondary btn-sm w-50 py-1" style="font-size: 0.775rem;">
                                    Edit Class
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4 text-muted">
                    <p class="mb-2">No academic classes found in database.</p>
                    <a href="{{ route('admin.classes.create') }}" class="btn btn-primary btn-sm">Create Class</a>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Subjects & Chapters Quick View -->
@if ($recentSubjects->isNotEmpty())
    <div class="admin-card mb-4">
        <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
            <div>
                <h3 class="admin-card-title mb-0">Recently Updated Subjects</h3>
                <span class="text-muted small">Quick syllabus navigation</span>
            </div>
            <a href="{{ route('admin.subjects.index') }}" class="small fw-semibold text-decoration-none">
                View All Subjects &rarr;
            </a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Academic Class</th>
                        <th>Code</th>
                        <th>Chapters</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentSubjects as $subj)
                        <tr>
                            <td class="fw-semibold text-nowrap" style="color: var(--color-navy-900);">
                                {{ $subj->name }}
                            </td>
                            <td class="text-nowrap">
                                <span class="badge rounded-pill bg-light text-dark border">
                                    {{ $subj->academicClass?->name ?? 'Unassigned' }}
                                </span>
                            </td>
                            <td class="font-monospace small text-muted text-nowrap">
                                {{ $subj->code ?: '—' }}
                            </td>
                            <td class="text-nowrap">
                                <span class="badge rounded-pill bg-primary-subtle text-primary">
                                    {{ $subj->chapters_count }} Chapter(s)
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <span class="status-pill {{ $subj->is_active ? 'active' : 'inactive' }}">
                                    {{ $subj->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td style="text-align: right;" class="text-nowrap">
                                <a href="{{ route('admin.chapters.index', ['subject_id' => $subj->id]) }}" class="btn btn-sm btn-outline-primary px-2 py-1" style="font-size: 0.775rem;">
                                    Manage Chapters
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Recent Notes & PDFs Quick View -->
@if ($recentNotes->isNotEmpty())
    <div class="admin-card mb-4">
        <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
            <div>
                <h3 class="admin-card-title mb-0">Recently Uploaded Notes &amp; PDFs</h3>
                <span class="text-muted small">Curated syllabus documents</span>
            </div>
            <a href="{{ route('admin.notes.index') }}" class="small fw-semibold text-decoration-none">
                View All Notes &rarr;
            </a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Hierarchy</th>
                        <th>File Size</th>
                        <th>Status</th>
                        <th>Uploaded</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentNotes as $rn)
                        <tr>
                            <td class="fw-semibold text-nowrap" style="color: var(--color-navy-900);">
                                <div class="d-flex align-items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    <span>{{ $rn->title }}</span>
                                </div>
                            </td>
                            <td class="text-nowrap small text-muted">
                                {{ $rn->academic_class?->name ?? 'Class' }} &raquo;
                                {{ $rn->chapter?->subject?->name ?? 'Subject' }} &raquo;
                                <span class="fw-medium text-dark">{{ $rn->chapter?->name ?? 'Chapter' }}</span>
                            </td>
                            <td class="font-monospace small text-muted text-nowrap">
                                {{ $rn->formatted_file_size }}
                            </td>
                            <td class="text-nowrap">
                                <span class="status-pill {{ $rn->status === 'published' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($rn->status) }}
                                </span>
                            </td>
                            <td class="text-nowrap small text-muted">
                                {{ $rn->created_at->format('M d, Y') }}
                            </td>
                            <td style="text-align: right;" class="text-nowrap">
                                <a href="{{ route('admin.notes.preview', $rn) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2 py-1" style="font-size: 0.775rem;">
                                    Preview
                                </a>
                                <a href="{{ route('admin.notes.edit', $rn) }}" class="btn btn-sm btn-outline-primary px-2 py-1 ms-1" style="font-size: 0.775rem;">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Recent Administrative Activity Log Table -->
<div class="admin-card mb-4">
    <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-1">
        <h3 class="admin-card-title mb-0">Recent Administrative Audit Activity</h3>
        <a href="{{ route('admin.administrators.index') }}" class="small fw-semibold text-decoration-none">View All Activity &rarr;</a>
    </div>

    @if ($recentAuditLogs->isEmpty())
        <div class="p-4 p-sm-5 text-center text-muted">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p class="mb-0">No administrative audit logs recorded yet.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="text-nowrap">Timestamp</th>
                        <th>Action</th>
                        <th>Administrator</th>
                        <th>Event Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentAuditLogs as $log)
                        <tr>
                            <td class="text-nowrap text-muted small">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td>
                                <span class="status-pill {{ str_contains($log->action, 'login') ? 'active' : (str_contains($log->action, 'deactivated') ? 'inactive' : 'pending') }}">
                                    {{ str_replace('_', ' ', strtoupper($log->action)) }}
                                </span>
                            </td>
                            <td class="fw-semibold text-nowrap" style="color: var(--color-navy-900);">
                                {{ $log->user?->name ?? 'System / Anonymous' }}
                            </td>
                            <td>
                                {{ $log->description }}
                            </td>
                            <td class="font-monospace small text-muted text-nowrap">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
