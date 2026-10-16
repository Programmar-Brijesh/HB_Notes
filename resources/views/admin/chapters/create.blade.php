@extends('layouts.admin')

@section('title', 'Add Academic Chapter - HB Notes')
@section('page-title', 'Create Chapter')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="admin-card-title mb-1">New Academic Chapter</h3>
                    <p class="text-muted small mb-0">Create a chapter unit for a specific curriculum subject.</p>
                </div>
                <a href="{{ route('admin.chapters.index', ['subject_id' => $selectedSubjectId]) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Chapters
                </a>
            </div>

            <div class="p-3 p-sm-4">
                <form method="POST" action="{{ route('admin.chapters.store') }}" data-auth-form>
                    @csrf

                    <!-- Subject Selection -->
                    <div class="mb-3">
                        <label for="subject_id" class="form-label small fw-semibold">Subject &amp; Class <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                            <option value="">Select Subject...</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (string)old('subject_id', $selectedSubjectId) === (string)$subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} — {{ $subject->academicClass?->name ?? 'Class' }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Chapter Number -->
                        <div class="col-12 col-sm-4">
                            <label for="chapter_number" class="form-label small fw-semibold">Chapter / Unit Number <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                name="chapter_number"
                                id="chapter_number"
                                class="form-control @error('chapter_number') is-invalid @enderror"
                                placeholder="e.g. 1"
                                min="1"
                                max="150"
                                value="{{ old('chapter_number') }}"
                                required
                            >
                            @error('chapter_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Chapter Name -->
                        <div class="col-12 col-sm-8">
                            <label for="name" class="form-label small fw-semibold">Chapter Name / Title <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. Real Numbers, Motion in a Plane"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label small fw-semibold">Chapter Overview &amp; Learning Objectives</label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Key topics covered, formulas, or NCERT section references..."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4 align-items-center">
                        <!-- Display Order -->
                        <div class="col-12 col-sm-6">
                            <label for="order_column" class="form-label small fw-semibold">Display Sort Order</label>
                            <input
                                type="number"
                                name="order_column"
                                id="order_column"
                                class="form-control @error('order_column') is-invalid @enderror"
                                placeholder="Optional custom sort index"
                                min="0"
                                value="{{ old('order_column') }}"
                            >
                            <div class="form-text small text-muted">Defaults to chapter number if left blank.</div>
                            @error('order_column')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Switch -->
                        <div class="col-12 col-sm-6">
                            <div class="form-check form-switch pt-sm-4">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="is_active"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                >
                                <label class="form-check-label fw-semibold small" for="is_active">
                                    Chapter Active in Curriculum
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('admin.chapters.index', ['subject_id' => $selectedSubjectId]) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4" id="save-chapter-btn">
                            <span class="spinner" aria-hidden="true"></span>
                            <span class="btn-text">Create Chapter</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
