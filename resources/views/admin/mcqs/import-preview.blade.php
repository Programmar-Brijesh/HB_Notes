@extends('layouts.admin')

@section('title', 'Validate & Preview Import - HB Notes')
@section('page-title', 'CSV Validation & Preview')

@section('content')
<!-- Header Card & Academic Target -->
<div class="admin-card mb-4">
    <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
        <div>
            <h3 class="admin-card-title mb-1">CSV Validation &amp; Pre-Import Audit</h3>
            <span class="text-muted small">File: <code>{{ $importLog->file_name }}</code></span>
        </div>
        <div>
            <span class="badge bg-primary fs-6 px-3 py-2">
                {{ $chapter->subject?->academicClass?->name ?? 'Class' }} &raquo;
                {{ $chapter->subject?->name ?? 'Subject' }} &raquo;
                Ch {{ $chapter->chapter_number }}: {{ $chapter->name }}
            </span>
        </div>
    </div>

    <!-- Validation Stats Metric Cards -->
    <div class="p-4 border-bottom bg-light">
        <div class="row g-3">
            <!-- Total Rows -->
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded border shadow-sm h-100">
                    <span class="text-muted small d-block mb-1">Total Data Rows</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $parseResult['total_rows'] }}</h3>
                </div>
            </div>

            <!-- Valid Rows Ready to Import -->
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded border border-success-subtle shadow-sm h-100">
                    <span class="text-success small fw-semibold d-block mb-1">Valid (Ready to Save)</span>
                    <h3 class="fw-bold mb-0 text-success">{{ $parseResult['valid_count'] }}</h3>
                </div>
            </div>

            <!-- Duplicate Rows -->
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded border border-warning-subtle shadow-sm h-100">
                    <span class="text-warning-emphasis small fw-semibold d-block mb-1">Duplicates (Will Skip)</span>
                    <h3 class="fw-bold mb-0 text-warning-emphasis">{{ $parseResult['duplicate_count'] }}</h3>
                </div>
            </div>

            <!-- Invalid Format Rows -->
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded border border-danger-subtle shadow-sm h-100">
                    <span class="text-danger small fw-semibold d-block mb-1">Invalid Format (Will Skip)</span>
                    <h3 class="fw-bold mb-0 text-danger">{{ $parseResult['invalid_count'] }}</h3>
                </div>
            </div>
        </div>

        <!-- Import Policy Alert -->
        <div class="alert {{ $parseResult['valid_count'] > 0 ? 'alert-info' : 'alert-danger' }} small mt-3 mb-0 d-flex align-items-center gap-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <div>
                <strong>Import Policy:</strong>
                @if ($parseResult['valid_count'] > 0)
                    Only the <strong>{{ $parseResult['valid_count'] }} valid, unique questions</strong> will be committed to the Question Bank upon confirmation. Any duplicate or invalid rows will be safely skipped and included in the post-import audit report.
                @else
                    No valid rows were detected in this file. Please review the errors below, correct the CSV, and upload again.
                @endif
            </div>
        </div>
    </div>

    <!-- Preview of Valid Questions -->
    @if (!empty($parseResult['preview_rows']))
        <div class="p-4 border-bottom">
            <h5 class="fw-bold text-dark mb-3">
                Sample Valid Questions Preview (Showing first {{ count($parseResult['preview_rows']) }} of {{ $parseResult['valid_count'] }})
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Question Text</th>
                            <th>Options</th>
                            <th class="text-center" style="width: 80px;">Answer</th>
                            <th style="width: 90px;">Difficulty</th>
                            <th style="width: 90px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($parseResult['preview_rows'] as $pr)
                            <tr>
                                <td class="font-monospace text-muted">{{ $pr['row_number'] }}</td>
                                <td class="fw-medium text-dark">{{ $pr['question'] }}</td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div><span class="badge {{ $pr['correct_option'] === 'A' ? 'bg-success' : 'bg-light text-dark border' }}">A</span> {{ $pr['option_a'] }}</div>
                                        <div><span class="badge {{ $pr['correct_option'] === 'B' ? 'bg-success' : 'bg-light text-dark border' }}">B</span> {{ $pr['option_b'] }}</div>
                                        <div><span class="badge {{ $pr['correct_option'] === 'C' ? 'bg-success' : 'bg-light text-dark border' }}">C</span> {{ $pr['option_c'] }}</div>
                                        <div><span class="badge {{ $pr['correct_option'] === 'D' ? 'bg-success' : 'bg-light text-dark border' }}">D</span> {{ $pr['option_d'] }}</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6">Option {{ $pr['correct_option'] }}</span>
                                </td>
                                <td><span class="badge bg-secondary">{{ ucfirst($pr['difficulty']) }}</span></td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($pr['status']) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Row-Specific Validation & Duplicate Errors List -->
    @php
        $combinedErrors = array_merge($parseResult['invalid_rows'], $parseResult['duplicate_rows']);
        usort($combinedErrors, fn($a, $b) => ($a['row'] ?? 0) <=> ($b['row'] ?? 0));
    @endphp

    @if (!empty($combinedErrors))
        <div class="p-4 border-bottom bg-danger-subtle bg-opacity-10">
            <h5 class="fw-bold text-danger mb-3 d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                Row-Specific Audit Warnings &amp; Exclusions ({{ count($combinedErrors) }} rows skipped)
            </h5>
            <div class="table-responsive bg-white rounded border">
                <table class="table table-sm align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 90px;">CSV Row</th>
                            <th style="width: 250px;">Exclusion Reason</th>
                            <th>Question Content Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($combinedErrors as $err)
                            <tr>
                                <td class="font-monospace fw-bold text-danger">Row {{ $err['row'] }}</td>
                                <td>
                                    <span class="text-danger fw-semibold">{{ $err['reason'] }}</span>
                                </td>
                                <td class="text-muted font-monospace text-truncate" style="max-width: 400px;">
                                    {{ $err['question'] ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Two Explicit Actions: Cancel vs Confirm -->
    <div class="p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
        <!-- Cancel Action -->
        <form method="POST" action="{{ route('admin.mcqs.import.cancel') }}">
            @csrf
            <input type="hidden" name="import_token" value="{{ $importLog->import_token }}">
            <button type="submit" class="btn btn-outline-danger px-4">
                &times; Cancel Import
            </button>
        </form>

        <!-- Confirm Action -->
        @if ($parseResult['valid_count'] > 0)
            <form method="POST" action="{{ route('admin.mcqs.import.confirm') }}">
                @csrf
                <input type="hidden" name="import_token" value="{{ $importLog->import_token }}">
                <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                <button type="submit" class="btn btn-success px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Confirm &amp; Import {{ $parseResult['valid_count'] }} Question(s)</span>
                </button>
            </form>
        @else
            <a href="{{ route('admin.mcqs.import') }}" class="btn btn-primary px-4">
                Upload a Different CSV
            </a>
        @endif
    </div>
</div>
@endsection
