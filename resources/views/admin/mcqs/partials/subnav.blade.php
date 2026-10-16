<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4 bg-white p-2 p-md-3 rounded border shadow-sm">
    <div class="nav nav-pills flex-wrap gap-1">
        <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.mcqs.index') ? 'active fw-semibold' : 'text-secondary' }}" href="{{ route('admin.mcqs.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <span>Question Bank</span>
        </a>

        <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.mcqs.create') || request()->routeIs('admin.mcqs.edit') ? 'active fw-semibold' : 'text-secondary' }}" href="{{ route('admin.mcqs.create') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Add MCQ</span>
        </a>

        <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.mcqs.import') || request()->routeIs('admin.mcqs.import.preview') ? 'active fw-semibold' : 'text-secondary' }}" href="{{ route('admin.mcqs.import') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            <span>Import MCQs (CSV)</span>
        </a>

        <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.mcqs.import.history') || request()->routeIs('admin.mcqs.import.report') ? 'active fw-semibold' : 'text-secondary' }}" href="{{ route('admin.mcqs.import.history') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span>Import History</span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.mcqs.import.template') }}" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm" title="Download official CSV template">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            <span>Download CSV Template</span>
        </a>
    </div>
</div>
