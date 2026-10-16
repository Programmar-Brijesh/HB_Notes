@extends('layouts.admin')

@section('title', 'Import Results Report - HB Notes')
@section('page-title', 'Bulk Import Execution Report')

@section('content')
<!-- Reusable MCQ Navigation Bar -->
@include('admin.mcqs.partials.subnav')

<div class="row justify-content-center">
    <div class="col-12 col-lg-10 col-xl-9">
        <div class="admin-card mb-4">
            <!-- Header with Status Badge -->
            <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                <div>
                    <h3 class="admin-card-title mb-1">Import Execution Audit Report</h3>
                    <span class="text-muted small">File: <code>{{ $import->file_name }}</code> &bull; {{ $import->confirmed_at ? $import->confirmed_at->format('M d, Y H:i:s') : $import->created_at->format('M d, Y H:i:s') }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if ($import->status === 'completed')
                        <span class="badge bg-success fs-6 px-3 py-2">
                            &#10003; Completed Successfully
                        </span>
                    @elseif ($import->status === 'completed_with_errors')
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            &#9888; Completed With Warnings
                        </span>
                    @elseif ($import->status === 'failed')
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            &#10007; Import Failed
                        </span>
                    @else
                        <span class="badge bg-secondary fs-6 px-3 py-2">
                            {{ ucfirst($import->status) }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Target Academic Hierarchy Banner -->
            <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="small">
                    <span class="text-muted">Target Hierarchy:</span>
                    <strong class="text-dark ms-1">
                        {{ $import->chapter?->subject?->academicClass?->name ?? 'Class' }}
                    </strong>
                    <span class="text-muted mx-1">&raquo;</span>
                    <strong class="text-dark">
                        {{ $import->chapter?->subject?->name ?? 'Subject' }}
                    </strong>
                    <span class="text-muted mx-1">&raquo;</span>
                    <strong class="text-primary">
                        Ch {{ $import->chapter?->chapter_number }}: {{ $import->chapter?->name }}
                    </strong>
                </div>
                <div class="small text-muted">
                    Imported by: <span class="fw-semibold text-dark">{{ $import->user?->name ?? 'Admin' }}</span>
                </div>
            </div>

            <!-- Quantitative Execution Statistics -->
            <div class="p-4 border-bottom">
                <div class="row g-3">
                    <!-- Total Rows Processed -->
                    <div class="col-6 col-md-4 col-xl">
                        <div class="p-3 bg-white rounded border shadow-sm h-100">
                            <span class="text-muted small d-block mb-1">Total CSV Rows</span>
                            <h3 class="fw-bold mb-0 text-dark">{{ $import->total_rows }}</h3>
                        </div>
                    </div>

                    <!-- Successfully Imported -->
                    <div class="col-6 col-md-4 col-xl">
                        <div class="p-3 bg-white rounded border border-success-subtle shadow-sm h-100">
                            <span class="text-success small fw-semibold d-block mb-1">Imported to Bank</span>
                            <h3 class="fw-bold mb-0 text-success">{{ $import->imported_count }}</h3>
                        </div>
                    </div>

                    <!-- Duplicates Skipped -->
                    <div class="col-6 col-md-4 col-xl">
                        <div class="p-3 bg-white rounded border border-warning-subtle shadow-sm h-100">
                            <span class="text-warning-emphasis small fw-semibold d-block mb-1">Duplicates Skipped</span>
                            <h3 class="fw-bold mb-0 text-warning-emphasis">{{ $import->duplicate_count }}</h3>
                        </div>
                    </div>

                    <!-- Invalid Format Rows -->
                    <div class="col-6 col-md-6 col-xl">
                        <div class="p-3 bg-white rounded border border-danger-subtle shadow-sm h-100">
                            <span class="text-danger small fw-semibold d-block mb-1">Validation Errors</span>
                            <h3 class="fw-bold mb-0 text-danger">{{ $import->invalid_count }}</h3>
                        </div>
                    </div>

                    <!-- Database Failures -->
                    <div class="col-6 col-md-6 col-xl">
                        <div class="p-3 bg-white rounded border border-secondary-subtle shadow-sm h-100">
                            <span class="text-secondary small fw-semibold d-block mb-1">DB Failures</span>
                            <h3 class="fw-bold mb-0 text-secondary">{{ $import->failed_count }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Error Log & CSV Export -->
            @php
                $importErrors = $import->error_data ?? [];
            @endphp

            @if (!empty($importErrors))
                <div class="p-4 border-bottom">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Row-Specific Exclusions ({{ count($importErrors) }})</h5>
                            <span class="text-muted small">The following rows were safely omitted during processing.</span>
                        </div>
                        <a href="{{ route('admin.mcqs.import.errors-csv', $import) }}" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            <span>Download Error Report (CSV)</span>
                        </a>
                    </div>

                    <div class="table-responsive bg-white rounded border">
                        <table class="table table-sm align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">CSV Row</th>
                                    <th style="width: 240px;">Exclusion Reason</th>
                                    <th>Question Content Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($importErrors as $err)
                                    <tr>
                                        <td class="font-monospace fw-bold text-danger">Row {{ $err['row'] ?? '—' }}</td>
                                        <td>
                                            <span class="text-danger fw-semibold">{{ $err['reason'] ?? 'Invalid data' }}</span>
                                        </td>
                                        <td class="text-muted font-monospace text-truncate" style="max-width: 400px;">
                                            {{ $err['question'] ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Navigation Actions -->
            <div class="p-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.mcqs.import.history') }}" class="btn btn-outline-secondary">
                        &larr; View Import History
                    </a>
                    <a href="{{ route('admin.mcqs.import') }}" class="btn btn-outline-primary">
                        Import Another CSV
                    </a>
                </div>
                <a href="{{ route('admin.mcqs.index', ['chapter_id' => $import->chapter_id]) }}" class="btn btn-primary px-4">
                    View Chapter Questions in Bank &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

