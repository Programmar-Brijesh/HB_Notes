<nav aria-label="Administrator Navigation">
    <div class="admin-nav-section-title">Overview</div>
    <ul class="admin-nav-list">
        <li class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>

    <div class="admin-nav-section-title">Academic Structure</div>
    <ul class="admin-nav-list">
        <li class="admin-nav-item {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
            <a href="{{ route('admin.classes.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span>Classes</span>
            </a>
        </li>
        <li class="admin-nav-item {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
            <a href="{{ route('admin.subjects.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
                <span>Subjects</span>
            </a>
        </li>
        <li class="admin-nav-item {{ request()->routeIs('admin.chapters.*') ? 'active' : '' }}">
            <a href="{{ route('admin.chapters.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                <span>Chapters</span>
            </a>
        </li>
    </ul>

    <div class="admin-nav-section-title">Educational Content</div>
    <ul class="admin-nav-list">
        <li class="admin-nav-item {{ request()->routeIs('admin.notes.*') ? 'active' : '' }}">
            <a href="{{ route('admin.notes.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span>Notes &amp; PDFs</span>
            </a>
        </li>
    </ul>

    <div class="admin-nav-section-title">MCQ Question Bank</div>
    <ul class="admin-nav-list">
        <li class="admin-nav-item {{ request()->routeIs('admin.mcqs.index') || request()->routeIs('admin.mcqs.show') ? 'active' : '' }}">
            <a href="{{ route('admin.mcqs.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span>Question Bank</span>
            </a>
        </li>
        <li class="admin-nav-item {{ request()->routeIs('admin.mcqs.create') || request()->routeIs('admin.mcqs.edit') ? 'active' : '' }}">
            <a href="{{ route('admin.mcqs.create') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add MCQ</span>
            </a>
        </li>
        <li class="admin-nav-item {{ request()->routeIs('admin.mcqs.import') || request()->routeIs('admin.mcqs.import.preview') ? 'active' : '' }}">
            <a href="{{ route('admin.mcqs.import') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span>Import MCQs (CSV)</span>
            </a>
        </li>
        <li class="admin-nav-item {{ request()->routeIs('admin.mcqs.import.history') || request()->routeIs('admin.mcqs.import.report') ? 'active' : '' }}">
            <a href="{{ route('admin.mcqs.import.history') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>Import History</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="{{ route('admin.mcqs.import.template') }}" title="Download official CSV template">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>CSV Template</span>
            </a>
        </li>
    </ul>

    <div class="admin-nav-section-title">Access &amp; Security</div>
    <ul class="admin-nav-list">
        <li class="admin-nav-item {{ request()->routeIs('admin.administrators.*') ? 'active' : '' }}">
            <a href="{{ route('admin.administrators.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Administrators</span>
            </a>
        </li>
    </ul>
</nav>
