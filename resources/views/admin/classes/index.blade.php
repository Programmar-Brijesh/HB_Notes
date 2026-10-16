@extends('layouts.admin')

@section('title', 'Manage Classes - HB Notes')
@section('page-title', 'Academic Classes')

@section('content')
<!-- Header Card with Search & Add Class Action -->
<div class="admin-card mb-4">
    <div class="admin-card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h3 class="admin-card-title mb-1">Curriculum Classes ({{ $classes->total() }})</h3>
            <p class="text-muted small mb-0">Manage secondary and senior secondary classes (Classes 9 to 12).</p>
        </div>
        <div>
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3 py-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add Academic Class</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="p-3 p-sm-4 border-bottom bg-light">
        <form method="GET" action="{{ route('admin.classes.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-sm-6 col-md-5">
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
                        placeholder="Search class name, stream, or description..."
                        value="{{ $search }}"
                    >
                </div>
            </div>

            <div class="col-6 col-sm-3 col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div class="col-6 col-sm-3 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-3">Filter</button>
                @if (!empty($search) || $status !== null && $status !== '')
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Classes Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="text-nowrap">Class Tier</th>
                    <th class="text-nowrap">Numeric Level</th>
                    <th class="text-nowrap">Stream Category</th>
                    <th class="text-nowrap">Subjects Count</th>
                    <th>Status</th>
                    <th style="text-align: right;" class="text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $class)
                    <tr>
                        <td class="text-nowrap">
                            <div class="fw-bold" style="color: var(--color-navy-900); font-size: 1rem;">
                                {{ $class->name }}
                            </div>
                            <div class="text-muted small" style="max-width: 280px; white-space: normal;">
                                {{ $class->description ?: 'No description provided' }}
                            </div>
                        </td>
                        <td class="text-nowrap">
                            <span class="badge rounded-pill bg-light text-dark border px-3 py-1 font-monospace">
                                Class {{ $class->class_number }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <span class="text-muted small fw-semibold">
                                {{ $class->stream ?: 'General' }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.subjects.index', ['class_id' => $class->id]) }}" class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-3 py-1">
                                {{ $class->subjects_count }} Subject(s) &rarr;
                            </a>
                        </td>
                        <td class="text-nowrap">
                            <span class="status-pill {{ $class->is_active ? 'active' : 'inactive' }}">
                                {{ $class->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td style="text-align: right;" class="text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('admin.subjects.create', ['class_id' => $class->id]) }}" class="btn btn-sm btn-outline-primary px-2 py-1" title="Add Subject">
                                    + Subject
                                </a>
                                <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Edit Class">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete {{ $class->name }}? Classes with existing subjects cannot be deleted.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete Class">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            No academic classes found matching criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($classes->hasPages())
        <div class="p-3 border-top d-flex justify-content-center">
            {{ $classes->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
