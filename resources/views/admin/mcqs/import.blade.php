@extends('layouts.admin')

@section('title', 'Bulk MCQ CSV Import - HB Notes')
@section('page-title', 'Bulk Import MCQ Questions')

@section('content')
<!-- Reusable MCQ Navigation Bar -->
@include('admin.mcqs.partials.subnav')

<div class="row justify-content-center">
    <div class="col-12 col-lg-10 col-xl-9">
        <div class="admin-card mb-4">
            <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                <div>
                    <h3 class="admin-card-title mb-1">Bulk MCQ CSV Import Wizard</h3>
                    <span class="text-muted small">Import dozens or hundreds of chapter-aligned questions in seconds via CSV.</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('admin.mcqs.import.history') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Import History</span>
                    </a>
                    <a href="{{ route('admin.mcqs.import.template') }}" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Download Official Template</span>
                    </a>
                    <a href="{{ route('admin.mcqs.index') }}" class="btn btn-outline-secondary btn-sm">
                        &larr; Back to Question Bank
                    </a>
                </div>
            </div>

            <div class="p-4">
                <form method="POST" action="{{ route('admin.mcqs.import.preview') }}" enctype="multipart/form-data" id="mcqImportForm">
                    @csrf

                    <!-- Step 1: Select Class -->
                    <div class="card p-3 mb-3 border rounded-3 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">1</span>
                            <label for="class_id" class="form-label fw-semibold text-dark mb-0">
                                Step 1: Select Academic Class <span class="text-danger">*</span>
                            </label>
                        </div>
                        <select id="class_id" name="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                            <option value="">-- Choose Class --</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}" {{ (string)old('class_id', $selectedClassId) === (string)$c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->stream ?: 'Curriculum' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Select the target student grade. Changing the class resets downstream subject and chapter selections.</div>
                        @error('class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Step 2: Select Subject -->
                    <div class="card p-3 mb-3 border rounded-3 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">2</span>
                            <label for="subject_id" class="form-label fw-semibold text-dark mb-0">
                                Step 2: Select Curriculum Subject <span class="text-danger">*</span>
                            </label>
                        </div>
                        <select id="subject_id" name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" disabled required>
                            <option value="">-- Select Class First --</option>
                        </select>
                        <div class="form-text small">Loads active curriculum subjects for the selected class. Changing the subject resets the chapter.</div>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Step 3: Select Chapter -->
                    <div class="card p-3 mb-3 border rounded-3 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">3</span>
                            <label for="chapter_id" class="form-label fw-semibold text-dark mb-0">
                                Step 3: Select Educational Chapter <span class="text-danger">*</span>
                            </label>
                        </div>
                        <select id="chapter_id" name="chapter_id" class="form-select @error('chapter_id') is-invalid @enderror" disabled required>
                            <option value="">-- Select Subject First --</option>
                        </select>
                        <div class="form-text small text-primary fw-medium">
                            Mandatory rule: Every question in the uploaded CSV will automatically belong to this chapter. The CSV does not require separate class, subject, or chapter columns.
                        </div>
                        @error('chapter_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Step 4: Upload CSV -->
                    <div class="card p-3 mb-4 border rounded-3 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">4</span>
                            <label for="csv_file" class="form-label fw-semibold text-dark mb-0">
                                Step 4: Upload Questions CSV Document <span class="text-danger">*</span>
                            </label>
                        </div>
                        <input
                            type="file"
                            class="form-control @error('csv_file') is-invalid @enderror"
                            id="csv_file"
                            name="csv_file"
                            accept=".csv,text/csv"
                            disabled
                            required
                        >
                        <div class="form-text small mt-2">
                            Standard CSV format only. Max file size: <strong>{{ round($maxSizeKb / 1024) }} MB</strong> (up to <strong>{{ $maxRows }} questions</strong> per batch).
                        </div>
                        @error('csv_file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- CSV Specification Accordion -->
                    <div class="alert alert-info border-0 rounded-3 mb-4">
                        <h6 class="fw-bold d-flex align-items-center gap-2 text-dark mb-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            Expected CSV Column Format
                        </h6>
                        <p class="small mb-2 text-dark">
                            Your CSV must include these exact column headers in the first row:
                        </p>
                        <div class="table-responsive bg-white rounded border p-2 mb-2">
                            <code class="text-primary small" style="white-space: nowrap;">
                                question,option_a,option_b,option_c,option_d,correct_option,explanation,difficulty,status
                            </code>
                        </div>
                        <ul class="small mb-0 text-muted ps-3">
                            <li><strong>correct_option:</strong> Must be <code>A</code>, <code>B</code>, <code>C</code>, or <code>D</code>.</li>
                            <li><strong>difficulty:</strong> <code>easy</code>, <code>medium</code>, or <code>hard</code> (defaults to medium).</li>
                            <li><strong>status:</strong> <code>draft</code> or <code>published</code> (defaults to published).</li>
                            <li><strong>Unicode Support:</strong> Supports Hindi, mathematical symbols, quotes, and commas inside quoted strings.</li>
                            <li><strong>UTF-8 BOM:</strong> Automatically handled if generated by Microsoft Excel.</li>
                        </ul>
                    </div>

                    <!-- Submit Action Button -->
                    <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                        <a href="{{ route('admin.mcqs.index') }}" class="btn btn-outline-secondary px-4">
                            Cancel
                        </a>
                        <button type="submit" id="submitBtn" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2" disabled>
                            <span>Validate &amp; Preview CSV</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Cascading Steps JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const classSelect = document.getElementById('class_id');
    const subjectSelect = document.getElementById('subject_id');
    const chapterSelect = document.getElementById('chapter_id');
    const fileInput = document.getElementById('csv_file');
    const submitBtn = document.getElementById('submitBtn');

    const lookupSubjectsUrl = "{{ route('admin.mcqs.lookup.subjects') }}";
    const lookupChaptersUrl = "{{ route('admin.mcqs.lookup.chapters') }}";

    const initialClassId = "{{ old('class_id', $selectedClassId) }}";
    const initialSubjectId = "{{ old('subject_id', $selectedSubjectId) }}";
    const initialChapterId = "{{ old('chapter_id', $selectedChapterId) }}";

    function updateStepControls() {
        const hasClass = Boolean(classSelect.value);
        const hasSubject = Boolean(subjectSelect.value);
        const hasChapter = Boolean(chapterSelect.value);
        const hasFile = Boolean(fileInput.files && fileInput.files.length > 0);

        fileInput.disabled = !(hasClass && hasSubject && hasChapter);
        submitBtn.disabled = !(hasClass && hasSubject && hasChapter && hasFile);
    }

    async function loadSubjects(classId, preselectSubjectId = null) {
        subjectSelect.innerHTML = '<option value="">Loading subjects...</option>';
        subjectSelect.disabled = true;
        chapterSelect.innerHTML = '<option value="">-- Select Subject First --</option>';
        chapterSelect.disabled = true;
        updateStepControls();

        if (!classId) {
            subjectSelect.innerHTML = '<option value="">-- Select Class First --</option>';
            return;
        }

        try {
            const res = await fetch(`${lookupSubjectsUrl}?class_id=${classId}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            subjectSelect.innerHTML = '<option value="">-- Choose Subject --</option>';
            if (data.subjects && data.subjects.length > 0) {
                data.subjects.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name + (s.code ? ` (${s.code})` : '');
                    if (preselectSubjectId && String(s.id) === String(preselectSubjectId)) {
                        opt.selected = true;
                    }
                    subjectSelect.appendChild(opt);
                });
                subjectSelect.disabled = false;

                if (preselectSubjectId) {
                    loadChapters(preselectSubjectId, initialChapterId);
                }
            } else {
                subjectSelect.innerHTML = '<option value="">No active subjects found in this class</option>';
            }
        } catch (e) {
            subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
        }
        updateStepControls();
    }

    async function loadChapters(subjectId, preselectChapterId = null) {
        chapterSelect.innerHTML = '<option value="">Loading chapters...</option>';
        chapterSelect.disabled = true;
        updateStepControls();

        if (!subjectId) {
            chapterSelect.innerHTML = '<option value="">-- Select Subject First --</option>';
            return;
        }

        try {
            const res = await fetch(`${lookupChaptersUrl}?subject_id=${subjectId}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            chapterSelect.innerHTML = '<option value="">-- Choose Chapter --</option>';
            if (data.chapters && data.chapters.length > 0) {
                data.chapters.forEach(ch => {
                    const opt = document.createElement('option');
                    opt.value = ch.id;
                    opt.textContent = `Ch ${ch.chapter_number}: ${ch.name}`;
                    if (preselectChapterId && String(ch.id) === String(preselectChapterId)) {
                        opt.selected = true;
                    }
                    chapterSelect.appendChild(opt);
                });
                chapterSelect.disabled = false;
            } else {
                chapterSelect.innerHTML = '<option value="">No active chapters found in this subject</option>';
            }
        } catch (e) {
            chapterSelect.innerHTML = '<option value="">Error loading chapters</option>';
        }
        updateStepControls();
    }

    classSelect.addEventListener('change', function () {
        loadSubjects(this.value);
    });

    subjectSelect.addEventListener('change', function () {
        loadChapters(this.value);
    });

    chapterSelect.addEventListener('change', updateStepControls);
    fileInput.addEventListener('change', updateStepControls);

    // Initial load if old input was present
    if (initialClassId) {
        loadSubjects(initialClassId, initialSubjectId);
    }
});
</script>
@endsection
