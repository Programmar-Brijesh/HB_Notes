@extends('layouts.admin')

@section('title', 'MCQ Import History - HB Notes')
@section('page-title', 'Bulk MCQ Import Audit History')

@section('content')
<!-- Reusable MCQ Navigation Bar -->
@include('admin.mcqs.partials.subnav')

<!-- Import History Table Card -->
<div class="admin-card mb-4 w-100">
    <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
        <div>
            <h3 class="admin-card-title mb-1">CSV Import Audit History ({{ $imports->total() }})</h3>
            <p class="text-muted small mb-0">Chronological audit log of bulk MCQ imports with validation metrics and detailed batch reports.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('admin.mcqs.import') }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 shadow-sm px-3 py-2 fw-semibold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span>Import New CSV</span>
            </a>
        </div>
    </div>

    <!-- Custom Styles for Responsive Import History Audit Table -->
    <style>
        .import-audit-table {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
        }
        .import-audit-table th,
        .import-audit-table td {
            padding: 0.75rem 0.5rem;
            vertical-align: middle;
        }
        .import-audit-table th:first-child,
        .import-audit-table td:first-child {
            padding-left: 1.25rem;
        }
        .import-audit-table th:last-child,
        .import-audit-table td:last-child {
            padding-right: 1.25rem;
        }
        .metric-badge-audit {
            font-size: 0.72rem;
            padding: 0.2rem 0.45rem;
            white-space: nowrap;
            font-weight: 500;
            border-radius: var(--radius-sm, 4px);
        }
    </style>

    <!-- Desktop / Tablet Table View (>= 768px) -->
    <div class="d-none d-md-block w-100">
        <table class="admin-table import-audit-table w-100">
            <thead>
                <tr>
                    <th style="width: 24%;">CSV Document</th>
                    <th style="width: 18%;">Target Hierarchy</th>
                    <th class="text-center" style="width: 12%;">Status</th>
                    <th class="text-center" style="width: 20%;">Execution Metrics</th>
                    <th style="width: 11%;">Imported By</th>
                    <th class="text-end" style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($imports as $import)
                    <tr>
                        <!-- CSV File Details -->
                        <td>
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <div class="p-1 px-2 rounded bg-light border text-primary flex-shrink-0">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="8" y1="13" x2="16" y2="13"></line>
                                        <line x1="8" y1="17" x2="16" y2="17"></line>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-semibold text-dark text-break" style="font-size: 0.85rem;" title="{{ $import->file_name }}">
                                        {{ $import->file_name }}
                                    </div>
                                    <span class="text-muted small font-monospace" style="font-size: 0.75rem;">Batch #{{ $import->id }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Target Academic Hierarchy -->
                        <td>
                            <div class="small min-w-0">
                                <div class="d-flex align-items-center gap-1 flex-wrap mb-1">
                                    <span class="badge rounded-pill bg-light text-dark border py-0 px-1" style="font-size: 0.72rem;">
                                        {{ $import->chapter?->subject?->academicClass?->name ?? 'Class' }}
                                    </span>
                                    <span class="fw-medium text-dark text-truncate" style="font-size: 0.8rem; max-width: 100%;">
                                        {{ $import->chapter?->subject?->name ?? 'Subject' }}
                                    </span>
                                </div>
                                <div class="text-muted small text-truncate" style="font-size: 0.75rem;" title="Ch {{ $import->chapter?->chapter_number }}: {{ $import->chapter?->name }}">
                                    Ch {{ $import->chapter?->chapter_number ?? '—' }}: {{ $import->chapter?->name ?? 'Chapter' }}
                                </div>
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="text-center">
                            @if ($import->status === 'completed')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 text-nowrap" style="font-size: 0.75rem;">
                                    &#10003; Completed
                                </span>
                            @elseif ($import->status === 'completed_with_errors')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 text-nowrap" style="font-size: 0.75rem;">
                                    &#9888; Warnings
                                </span>
                            @elseif ($import->status === 'failed')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 text-nowrap" style="font-size: 0.75rem;">
                                    &#10007; Failed
                                </span>
                            @elseif ($import->status === 'cancelled')
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 text-nowrap" style="font-size: 0.75rem;">
                                    Cancelled
                                </span>
                            @elseif ($import->status === 'processing')
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 text-nowrap" style="font-size: 0.75rem;">
                                    Processing...
                                </span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1 text-nowrap" style="font-size: 0.75rem;">
                                    {{ ucfirst($import->status) }}
                                </span>
                            @endif
                        </td>

                        <!-- Execution Metrics Breakdown -->
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                <span class="badge bg-light text-dark border metric-badge-audit" title="Total CSV Rows">Total: {{ $import->total_rows }}</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle metric-badge-audit" title="Imported Questions">Imported: {{ $import->imported_count }}</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle metric-badge-audit" title="Duplicates Skipped">Dupes: {{ $import->duplicate_count }}</span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle metric-badge-audit" title="Validation Format Errors">Invalid: {{ $import->invalid_count }}</span>
                                @if ($import->failed_count > 0)
                                    <span class="badge bg-secondary text-white metric-badge-audit" title="Database Failures">Failed: {{ $import->failed_count }}</span>
                                @endif
                            </div>
                        </td>

                        <!-- Admin User & Timestamp -->
                        <td>
                            <div class="small fw-medium text-dark text-truncate" style="font-size: 0.8rem;" title="{{ $import->user?->name }}">
                                {{ $import->user?->name ?? 'Admin' }}
                            </div>
                            <div class="text-muted small text-nowrap" style="font-size: 0.725rem;">
                                {{ ($import->confirmed_at ?? $import->created_at)->format('M d, Y') }}
                            </div>
                            <div class="text-muted text-nowrap" style="font-size: 0.675rem;">
                                {{ ($import->confirmed_at ?? $import->created_at)->format('h:i A') }}
                            </div>
                        </td>

                        <!-- Action Links -->
                        <td class="text-end">
                            <div class="d-inline-flex align-items-center justify-content-end gap-1 flex-wrap">
                                <!-- View Report Link -->
                                <a href="{{ route('admin.mcqs.import.report', $import) }}" class="btn btn-sm btn-primary px-2 py-1 d-inline-flex align-items-center gap-1 shadow-sm text-nowrap" style="font-size: 0.775rem;" title="Open detailed execution report">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                    </svg>
                                    <span>View Report</span>
                                </a>

                                <!-- Optional Error CSV Download if errors exist -->
                                @if (!empty($import->error_data))
                                    <a href="{{ route('admin.mcqs.import.errors-csv', $import) }}" class="btn btn-sm btn-outline-danger px-1.5 py-1 shadow-sm" style="font-size: 0.775rem;" title="Download Error Log (CSV)">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <p class="mb-3">No bulk CSV import logs recorded yet.</p>
                            <a href="{{ route('admin.mcqs.import') }}" class="btn btn-primary btn-sm">
                                Start First MCQ CSV Import
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (< 768px) -->
    <div class="d-md-none">
        @forelse ($imports as $import)
            <div class="p-3 border-bottom">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div class="d-flex align-items-start gap-2 min-w-0">
                        <div class="p-1 px-2 rounded bg-light border text-primary flex-shrink-0 mt-1">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="8" y1="13" x2="16" y2="13"></line>
                                <line x1="8" y1="17" x2="16" y2="17"></line>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="fw-bold text-dark text-break" style="font-size: 0.875rem;">
                                {{ $import->file_name }}
                            </div>
                            <span class="text-muted small font-monospace" style="font-size: 0.75rem;">Batch #{{ $import->id }}</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        @if ($import->status === 'completed')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.75rem;">
                                &#10003; Completed
                            </span>
                        @elseif ($import->status === 'completed_with_errors')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 0.75rem;">
                                &#9888; Warnings
                            </span>
                        @elseif ($import->status === 'failed')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 0.75rem;">
                                &#10007; Failed
                            </span>
                        @elseif ($import->status === 'cancelled')
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.75rem;">
                                Cancelled
                            </span>
                        @else
                            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.75rem;">
                                {{ ucfirst($import->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Target Hierarchy -->
                <div class="small mb-2 p-2 rounded bg-light">
                    <span class="badge bg-white text-dark border me-1">{{ $import->chapter?->subject?->academicClass?->name ?? 'Class' }}</span>
                    <span class="fw-medium text-dark">{{ $import->chapter?->subject?->name ?? 'Subject' }}</span>
                    <span class="text-muted mx-1">&raquo;</span>
                    <span class="text-primary fw-medium">Ch {{ $import->chapter?->chapter_number ?? '—' }}: {{ $import->chapter?->name ?? 'Chapter' }}</span>
                </div>

                <!-- Execution Metrics Breakdown -->
                <div class="d-flex align-items-center gap-1 flex-wrap small mb-3">
                    <span class="badge bg-light text-dark border metric-badge-audit" title="Total CSV Rows">Total: {{ $import->total_rows }}</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle metric-badge-audit" title="Imported Questions">Imported: {{ $import->imported_count }}</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle metric-badge-audit" title="Duplicates Skipped">Dupes: {{ $import->duplicate_count }}</span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle metric-badge-audit" title="Validation Format Errors">Invalid: {{ $import->invalid_count }}</span>
                    @if ($import->failed_count > 0)
                        <span class="badge bg-secondary text-white metric-badge-audit" title="Database Failures">Failed: {{ $import->failed_count }}</span>
                    @endif
                </div>

                <!-- Admin & Action Buttons -->
                <div class="d-flex align-items-center justify-content-between gap-2 pt-2 border-top flex-wrap">
                    <div class="text-muted small" style="font-size: 0.775rem;">
                        <span class="fw-semibold text-dark">{{ $import->user?->name ?? 'Admin' }}</span> &bull;
                        <span>{{ ($import->confirmed_at ?? $import->created_at)->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                        <a href="{{ route('admin.mcqs.import.report', $import) }}" class="btn btn-sm btn-primary px-3 py-1 d-inline-flex align-items-center gap-1 shadow-sm text-nowrap" style="font-size: 0.8rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                            <span>View Report</span>
                        </a>

                        @if (!empty($import->error_data))
                            <a href="{{ route('admin.mcqs.import.errors-csv', $import) }}" class="btn btn-sm btn-outline-danger px-2 py-1 shadow-sm" style="font-size: 0.8rem;" title="Download Error Log (CSV)">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <p class="mb-2">No bulk CSV import logs recorded yet.</p>
                <a href="{{ route('admin.mcqs.import') }}" class="btn btn-primary btn-sm">
                    Start First MCQ CSV Import
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination Footer -->
    @if ($imports->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">
                Showing {{ $imports->firstItem() }} to {{ $imports->lastItem() }} of {{ $imports->total() }} import batches
            </span>
            <div>
                {{ $imports->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection
