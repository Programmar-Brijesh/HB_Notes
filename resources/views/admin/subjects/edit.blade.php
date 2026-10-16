@extends('layouts.admin')

@section('title', 'Edit ' . $subject->name . ' - HB Notes')
@section('page-title', 'Edit Subject')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="admin-card-title mb-1">Edit Subject: {{ $subject->name }}</h3>
                    <p class="text-muted small mb-0">Update class assignment, code, syllabus, and active status.</p>
                </div>
                <a href="{{ route('admin.subjects.index', ['class_id' => $subject->academic_class_id]) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Subjects
                </a>
            </div>

            <div class="p-3 p-sm-4">
                <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" data-auth-form>
                    @csrf
                    @method('PUT')

                    <!-- Academic Class Selection -->
                    <div class="mb-3">
                        <label for="academic_class_id" class="form-label small fw-semibold">Academic Class Tier <span class="text-danger">*</span></label>
                        <select name="academic_class_id" id="academic_class_id" class="form-select @error('academic_class_id') is-invalid @enderror" required>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ (string)old('academic_class_id', $subject->academic_class_id) === (string)$class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ $class->stream ?: 'General' }})
                                </option>
                            @endforeach
                        </select>
                        @error('academic_class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Subject Name -->
                        <div class="col-12 col-sm-8">
                            <label for="name" class="form-label small fw-semibold">Subject Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. Mathematics, Physics, Chemistry"
                                value="{{ old('name', $subject->name) }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Subject Code -->
                        <div class="col-12 col-sm-4">
                            <label for="code" class="form-label small fw-semibold">Subject Code</label>
                            <input
                                type="text"
                                name="code"
                                id="code"
                                class="form-control @error('code') is-invalid @enderror"
                                placeholder="e.g. MATH-041, PHY-042"
                                value="{{ old('code', $subject->code) }}"
                            >
                            @error('code')
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
                            placeholder="Briefly outline subject scope, textbook reference, or syllabus guidelines..."
                        >{{ old('description', $subject->description) }}</textarea>
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
                                placeholder="0"
                                min="0"
                                value="{{ old('order_column', $subject->order_column) }}"
                            >
                            <div class="form-text small text-muted">Lower numbers appear first in lists.</div>
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
                                    {{ old('is_active', $subject->is_active) ? 'checked' : '' }}
                                >
                                <label class="form-check-label fw-semibold small" for="is_active">
                                    Subject Active in Curriculum
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <span class="text-muted small">Associated with {{ $subject->chapters()->count() }} chapter(s)</span>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.subjects.index', ['class_id' => $subject->academic_class_id]) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4" id="update-subject-btn">
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
