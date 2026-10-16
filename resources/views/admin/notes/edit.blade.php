@extends('layouts.admin')

@section('title', 'Edit Note - ' . $note->title . ' - HB Notes')
@section('page-title', 'Edit Educational Note')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="admin-card-title mb-1">Edit Note: {{ $note->title }}</h3>
                    <span class="text-muted small">Update note metadata or replace the attached PDF document.</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.notes.preview', $note) }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span>Preview PDF</span>
                    </a>
                    <a href="{{ route('admin.notes.index') }}" class="btn btn-outline-secondary btn-sm">
                        &larr; Back
                    </a>
                </div>
            </div>

            <div class="p-4">
                <form method="POST" action="{{ route('admin.notes.update', $note) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Hierarchy Selection: Class, Subject, Chapter -->
                    <div class="card p-3 bg-light border-0 mb-4 rounded-3">
                        <div class="fw-semibold text-dark small mb-3 text-uppercase" style="letter-spacing: 0.5px;">
                            Academic Curriculum Assignment
                        </div>

                        <div class="row g-3">
                            <!-- Academic Class Selector -->
                            <div class="col-12 col-md-4">
                                <label for="class_id" class="form-label small fw-semibold">Academic Class <span class="text-danger">*</span></label>
                                <select id="class_id" name="class_id" class="form-select @error('class_id') is-invalid @enderror">
                                    <option value="">Select Class...</option>
                                    @php
                                        $currentClassId = old('class_id', $note->chapter?->subject?->academic_class_id);
                                        $currentSubjectId = old('subject_id', $note->chapter?->subject_id);
                                        $currentChapterId = old('chapter_id', $note->chapter_id);
                                    @endphp
                                    @foreach ($classes as $c)
                                        <option value="{{ $c->id }}" {{ (string)$currentClassId === (string)$c->id ? 'selected' : '' }}>
                                            {{ $c->name }} ({{ $c->stream ?: 'General' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Subject Selector -->
                            <div class="col-12 col-md-4">
                                <label for="subject_id" class="form-label small fw-semibold">Subject <span class="text-danger">*</span></label>
                                <select id="subject_id" name="subject_id" class="form-select @error('subject_id') is-invalid @enderror">
                                    <option value="">Select Subject...</option>
                                    @foreach ($classes as $c)
                                        @foreach ($c->subjects as $s)
                                            <option
                                                value="{{ $s->id }}"
                                                data-class-id="{{ $c->id }}"
                                                {{ (string)$currentSubjectId === (string)$s->id ? 'selected' : '' }}
                                            >
                                                {{ $s->name }} [{{ $c->name }}]
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Chapter Selector -->
                            <div class="col-12 col-md-4">
                                <label for="chapter_id" class="form-label small fw-semibold">Chapter Unit <span class="text-danger">*</span></label>
                                <select id="chapter_id" name="chapter_id" class="form-select @error('chapter_id') is-invalid @enderror" required>
                                    <option value="">Select Chapter...</option>
                                    @foreach ($classes as $c)
                                        @foreach ($c->subjects as $s)
                                            @foreach ($s->chapters as $ch)
                                                <option
                                                    value="{{ $ch->id }}"
                                                    data-class-id="{{ $c->id }}"
                                                    data-subject-id="{{ $s->id }}"
                                                    {{ (string)$currentChapterId === (string)$ch->id ? 'selected' : '' }}
                                                >
                                                    Ch {{ $ch->chapter_number }}: {{ $ch->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('chapter_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Note Title & Slug -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-8">
                            <label for="title" class="form-label small fw-semibold">Note Title <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('title') is-invalid @enderror"
                                id="title"
                                name="title"
                                value="{{ old('title', $note->title) }}"
                                required
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="slug" class="form-label small fw-semibold">Slug</label>
                            <input
                                type="text"
                                class="form-control font-monospace @error('slug') is-invalid @enderror"
                                id="slug"
                                name="slug"
                                value="{{ old('slug', $note->slug) }}"
                            >
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label small fw-semibold">Description / Syllabus Summary <span class="text-muted">(Optional)</span></label>
                        <textarea
                            class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            rows="3"
                        >{{ old('description', $note->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Current PDF Document & Safe Replacement Box -->
                    <div class="card p-3 border-2 border-primary-subtle bg-light mb-4 rounded-3">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                            <div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1">Current Active Document</span>
                                <div class="fw-semibold text-dark">{{ $note->file_original_name }}</div>
                                <div class="small text-muted font-monospace">{{ $note->formatted_file_size }} &bull; Uploaded {{ $note->created_at->format('M d, Y H:i') }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admin.notes.preview', $note) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-3 py-1">
                                    Preview
                                </a>
                                <a href="{{ route('admin.notes.download', $note) }}" class="btn btn-sm btn-outline-primary px-3 py-1">
                                    Download
                                </a>
                            </div>
                        </div>

                        <div>
                            <label for="pdf_file" class="form-label small fw-semibold text-dark">
                                Replace PDF Document <span class="text-muted">(Optional — leave empty to keep current file)</span>
                            </label>
                            <input
                                type="file"
                                class="form-control @error('pdf_file') is-invalid @enderror"
                                id="pdf_file"
                                name="pdf_file"
                                accept="application/pdf"
                            >
                            <div class="form-text small mt-1">
                                Max replacement size: <strong>50 MB</strong>. Genuine PDF documents only. The existing file will be safely swapped only after the database update succeeds.
                            </div>
                            @error('pdf_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Thumbnail & Publication Settings -->
                    <div class="row g-3 mb-4">
                        <!-- Optional Thumbnail -->
                        <div class="col-12 col-md-6">
                            <label for="thumbnail" class="form-label small fw-semibold">Cover Thumbnail <span class="text-muted">(Optional)</span></label>
                            @if ($note->thumbnail_path)
                                <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded bg-white">
                                    <span class="small text-success fw-medium">Active Thumbnail Uploaded</span>
                                </div>
                            @endif
                            <input
                                type="file"
                                class="form-control @error('thumbnail') is-invalid @enderror"
                                id="thumbnail"
                                name="thumbnail"
                                accept="image/png,image/jpeg,image/webp"
                            >
                            <div class="form-text small">JPEG, PNG, or WebP up to 5 MB. Leave empty to keep existing.</div>
                            @error('thumbnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status: Draft vs Published -->
                        <div class="col-6 col-md-3">
                            <label for="status" class="form-label small fw-semibold">Publication Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="published" {{ old('status', $note->status) === 'published' ? 'selected' : '' }}>Published (Active)</option>
                                <option value="draft" {{ old('status', $note->status) === 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Display Order -->
                        <div class="col-6 col-md-3">
                            <label for="order_column" class="form-label small fw-semibold">Display Order</label>
                            <input
                                type="number"
                                class="form-control @error('order_column') is-invalid @enderror"
                                id="order_column"
                                name="order_column"
                                value="{{ old('order_column', $note->order_column) }}"
                                min="0"
                                max="999"
                            >
                            @error('order_column')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('admin.notes.index') }}" class="btn btn-outline-secondary px-4">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Cascading Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const classSelect = document.getElementById('class_id');
    const subjectSelect = document.getElementById('subject_id');
    const chapterSelect = document.getElementById('chapter_id');

    function filterSubjects() {
        const selectedClass = classSelect.value;
        const subjectOptions = subjectSelect.querySelectorAll('option');

        subjectOptions.forEach(opt => {
            if (!opt.value) return;
            const optClass = opt.getAttribute('data-class-id');
            if (!selectedClass || optClass === selectedClass) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
                if (opt.selected) {
                    subjectSelect.value = '';
                }
            }
        });
        filterChapters();
    }

    function filterChapters() {
        const selectedClass = classSelect.value;
        const selectedSubject = subjectSelect.value;
        const chapterOptions = chapterSelect.querySelectorAll('option');

        chapterOptions.forEach(opt => {
            if (!opt.value) return;
            const optClass = opt.getAttribute('data-class-id');
            const optSubject = opt.getAttribute('data-subject-id');

            let match = true;
            if (selectedClass && optClass !== selectedClass) match = false;
            if (selectedSubject && optSubject !== selectedSubject) match = false;

            if (match) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
                if (opt.selected) {
                    chapterSelect.value = '';
                }
            }
        });
    }

    classSelect.addEventListener('change', filterSubjects);
    subjectSelect.addEventListener('change', filterChapters);

    // Initial filter pass
    if (classSelect.value) {
        filterSubjects();
    }
});
</script>
@endsection
