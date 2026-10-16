<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Dashboard - HB Notes</title>
    <link rel="icon" type="image/png" href="{{ asset('images/hb_logo.png') }}">

    <!-- Local Bootstrap 5.3 Framework -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body style="background-color: var(--color-surface-bg);">
    <!-- Top Navigation Bar -->
    <nav class="app-navbar">
        <div class="navbar-container">
            <div class="nav-brand-group">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <img src="{{ asset('images/hb_logo.png') }}" alt="HB Notes" style="height: 36px; width: auto; object-fit: contain;">
                    <span style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; color: var(--color-navy-900);">HB Notes</span>
                </a>
            </div>

            <div class="nav-user-group d-flex align-items-center gap-2 gap-sm-3">
                <div class="user-badge d-flex align-items-center gap-2">
                    <div class="user-avatar-circle" style="width: 32px; height: 32px; font-size: 0.85rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <span class="user-name-text d-none d-sm-inline">{{ $user->name }}</span>
                    <span class="user-role-badge">{{ $user->role ?? 'User' }}</span>
                </div>

                <!-- POST-based Logout form with CSRF -->
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn-secondary d-flex align-items-center gap-1" id="logout-btn" style="height: 38px; padding: 0 0.85rem; font-size: 0.825rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span class="d-none d-sm-inline">Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="dashboard-hero py-4 py-md-5 px-3 px-sm-4">
        <div class="dashboard-hero-content container-lg">
            @if (session('status'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.4); color: #A7F3D0;" role="alert">
                    <svg class="alert-icon flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            <h1 class="dashboard-hero-title h2 fw-bold mb-2">Welcome back, {{ $user->name }}!</h1>
            <p class="dashboard-hero-subtitle mb-0">Select your academic class below to browse structured notes, formulas, and revision packs.</p>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="dashboard-main-container container-lg px-3 px-sm-4">
        <!-- Classes Grid -->
        <section aria-labelledby="classes-heading" class="mb-4">
            <h2 id="classes-heading" class="h5 fw-bold mb-3" style="color: var(--color-navy-900);">
                Select Academic Curriculum
            </h2>
            <div class="row g-3">
                @foreach ($classes as $item)
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="class-card h-100 d-flex flex-column justify-content-between p-3 p-sm-4">
                            <div>
                                <div class="class-card-header d-flex align-items-center justify-content-between mb-2">
                                    <h3 class="class-card-title h6 fw-bold mb-0">{{ $item['title'] }}</h3>
                                    <span class="class-card-badge">{{ $item['badge'] }}</span>
                                </div>
                                <p class="class-card-desc mb-3">{{ $item['desc'] }}</p>
                            </div>
                            <div>
                                <button type="button" class="btn-primary w-100" style="height: 40px; font-size: 0.85rem;" onclick="alert('Subject repository for {{ $item['title'] }} is ready for upcoming syllabus integration.')">
                                    Explore {{ $item['title'] }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Recent Resources Section -->
        <section class="resources-section mb-4 p-3 p-sm-4" aria-labelledby="resources-heading">
            <div class="section-header mb-3">
                <div>
                    <h2 id="resources-heading" class="section-title h5 fw-bold mb-1">Recently Updated Subject Notes</h2>
                    <p class="text-muted small mb-0">Curated by subject matter experts according to the latest educational syllabus.</p>
                </div>
            </div>

            <div class="resources-list">
                @foreach ($recentResources as $res)
                    <div class="resource-item d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 py-3 border-bottom">
                        <div class="resource-meta d-flex align-items-center gap-3">
                            <div class="resource-icon-box flex-shrink-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                            </div>
                            <div class="resource-info">
                                <h5 class="mb-0 fw-semibold" style="font-size: 0.95rem; color: var(--color-navy-900);">{{ $res['subject'] }}: {{ $res['topic'] }}</h5>
                                <p class="mb-0 text-muted small">{{ $res['class'] }} &bull; {{ $res['pages'] }} pages &bull; {{ $res['date'] }}</p>
                            </div>
                        </div>

                        <div>
                            <span class="badge rounded-pill text-primary bg-primary-subtle fw-semibold px-3 py-2" style="font-size: 0.75rem;">
                                Syllabus Aligned
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <footer class="text-center py-4 px-3 text-muted small border-top bg-white">
        <p class="mb-0">&copy; {{ date('Y') }} HB Notes. Educational platform for Classes 9–12.</p>
    </footer>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
