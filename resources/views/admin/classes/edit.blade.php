@extends('layouts.admin')

@section('title', 'Edit ' . $class->name . ' - HB Notes')
@section('page-title', 'Edit Academic Class')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="admin-card-title mb-1">Edit {{ $class->name }}</h3>
                    <p class="text-muted small mb-0">Modify class details, syllabus overview, and active status.</p>
                </div>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Classes
                </a>
            </div>

            <div class="p-3 p-sm-4">
                <form method="POST" action="{{ route('admin.classes.update', $class) }}" data-auth-form>
                    @csrf
                    @method('PUT')

                    <!-- Class Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold">Class Display Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control form-control-lg @error('name') is-invalid @enderror"
                            placeholder="e.g. Class 9"
                            value="{{ old('name', $class->name) }}"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Numeric Class Level -->
                        <div class="col-12 col-sm-6">
                            <label for="class_number" class="form-label small fw-semibold">Numeric Level (9–12) <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                name="class_number"
                                id="class_number"
                                class="form-control @error('class_number') is-invalid @enderror"
                                placeholder="e.g. 9"
                                min="1"
                                max="20"
                                value="{{ old('class_number', $class->class_number) }}"
                                required
                            >
                            <div class="form-text small text-muted">Unique numeric identifier preventing duplicate class tiers.</div>
                            @error('class_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Stream / Category -->
                        <div class="col-12 col-sm-6">
                            <label for="stream" class="form-label small fw-semibold">Stream / Category</label>
                            <input
                                type="text"
                                name="stream"
                                id="stream"
                                class="form-control @error('stream') is-invalid @enderror"
                                placeholder="e.g. Secondary (General), Science, Commerce"
                                value="{{ old('stream', $class->stream) }}"
                            >
                            @error('stream')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label small fw-semibold">Syllabus Overview &amp; Description</label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Provide a brief summary of subjects and academic objectives..."
                        >{{ old('description', $class->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Active Status Switch -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="is_active"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $class->is_active) ? 'checked' : '' }}
                            >
                            <label class="form-check-label fw-semibold small" for="is_active">
                                Class Active in Curriculum
                            </label>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <span class="text-muted small">Associated with {{ $class->subjects()->count() }} subject(s)</span>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4" id="update-class-btn">
                                <span class="spinner" aria-hidden="true"></span>
                                <span class="btn-text">Save Changes</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
