@extends('layouts.admin')

@section('title', 'Upload Note / PDF - HB Notes')
@section('page-title', 'Upload Educational Note')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="admin-card-title mb-1">Upload New PDF Note</h3>
                    <span class="text-muted small">Attach a curriculum PDF document to a syllabus chapter.</span>
                </div>
                <a href="{{ route('admin.notes.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Library
                </a>
            </div>

            <div class="p-4">
                <form method="POST" action="{{ route('admin.notes.store') }}" enctype="multipart/form-data">
                    @csrf

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
                                    @foreach ($classes as $c)
                                        <option value="{{ $c->id }}" {{ (string)old('class_id', $selectedClassId) === (string)$c->id ? 'selected' : '' }}>
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
                                                {{ (string)old('subject_id', $selectedSubjectId) === (string)$s->id ? 'selected' : '' }}
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
                                                    {{ (string)old('chapter_id', $selectedChapterId) === (string)$ch->id ? 'selected' : '' }}
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
                                value="{{ old('title') }}"
                                placeholder="e.g. Chapter 1 Comprehensive Handwritten Notes"
                                required
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="slug" class="form-label small fw-semibold">Slug <span class="text-muted">(Optional)</span></label>
                            <input
                                type="text"
                                class="form-control font-monospace @error('slug') is-invalid @enderror"
                                id="slug"
                                name="slug"
                                value="{{ old('slug') }}"
                                placeholder="Auto-generated from title"
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
                            placeholder="Briefly describe key topics, formula sheets, or exam revision highlights included in this note..."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- PDF Upload Section -->
                    <div class="card p-3 border-2 border-primary-subtle bg-primary-subtle bg-opacity-10 mb-4 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            <label for="pdf_file" class="form-label fw-semibold text-dark mb-0">
                                PDF Document Upload <span class="text-danger">*</span>
                            </label>
                        </div>
                        <input
                            type="file"
                            class="form-control @error('pdf_file') is-invalid @enderror"
                            id="pdf_file"
                            name="pdf_file"
                            accept="application/pdf"
                            required
                        >
                        <div class="form-text small mt-2">
                            Genuine PDF documents only. Max upload size: <strong>50 MB</strong>. Stored safely in private storage.
                        </div>
                        @error('pdf_file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Optional Thumbnail & Publication Settings -->
                    <div class="row g-3 mb-4">
                        <!-- Optional Thumbnail -->
                        <div class="col-12 col-md-6">
                            <label for="thumbnail" class="form-label small fw-semibold">Cover Thumbnail <span class="text-muted">(Optional)</span></label>
                            <input
                                type="file"
                                class="form-control @error('thumbnail') is-invalid @enderror"
                                id="thumbnail"
                                name="thumbnail"
                                accept="image/png,image/jpeg,image/webp"
                            >
                            <div class="form-text small">JPEG, PNG, or WebP up to 5 MB.</div>
                            @error('thumbnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status: Draft vs Published -->
                        <div class="col-6 col-md-3">
                            <label for="status" class="form-label small fw-semibold">Publication Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published (Active)</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
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
                                value="{{ old('order_column', 0) }}"
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
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <span>Upload &amp; Save Note</span>
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
            if (!opt.value) return; // Keep "Select Subject"
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
            if (!opt.value) return; // Keep "Select Chapter"
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

    // Initial pass if pre-selected
    if (classSelect.value) {
        filterSubjects();
    }
});
</script>
@endsection
