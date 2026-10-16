<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Admin Portal - HB Notes')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/hb_logo.png') }}">

    <!-- Local Bootstrap 5.3 Framework -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    <!-- Custom Administrative & Authentication Styles -->
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @stack('styles')
</head>
<body class="admin-body">
    <!-- Mobile Topbar (< 992px) -->
    <header class="admin-mobile-header d-lg-none sticky-top">
        <div class="d-flex align-items-center gap-2">
            <button class="btn admin-mobile-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileSidebar" aria-controls="adminMobileSidebar" aria-label="Toggle navigation menu">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <img src="{{ asset('images/hb_logo.png') }}" alt="HB Notes Admin Logo" style="height: 32px; width: auto; object-fit: contain;">
                <div class="d-flex flex-column">
                    <span class="admin-mobile-brand-title">HB Notes</span>
                    <span class="admin-mobile-brand-sub">Admin</span>
                </div>
            </a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="user-avatar-circle user-avatar-mobile" title="{{ Auth::user()->name }}">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger admin-mobile-logout-btn" title="Sign Out">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <!-- Mobile Offcanvas Drawer (< 992px) -->
    <div class="offcanvas offcanvas-start admin-offcanvas d-lg-none" tabindex="-1" id="adminMobileSidebar" aria-labelledby="adminMobileSidebarLabel">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center gap-2" id="adminMobileSidebarLabel">
                <img src="{{ asset('images/hb_logo.png') }}" alt="HB Notes" style="height: 36px; width: auto; object-fit: contain;">
                <div class="d-flex flex-column text-white">
                    <span style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; line-height: 1.2;">HB Notes</span>
                    <span style="font-size: 0.7rem; color: #60A5FA; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">Admin Portal</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
            @include('layouts.partials.admin-nav')

            <div class="admin-offcanvas-footer">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="user-avatar-circle" style="background: var(--color-primary); width: 36px; height: 36px; font-weight: 700;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="text-white fw-bold text-truncate" style="font-size: 0.875rem;">{{ Auth::user()->name }}</span>
                        <span class="text-white-50 text-truncate" style="font-size: 0.725rem;">{{ Auth::user()->email }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Sign Out of Portal</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Desktop Layout Shell -->
    <div class="admin-shell">
        <!-- Desktop Fixed Sidebar Navigation (>= 992px) -->
        <aside class="admin-sidebar d-none d-lg-flex">
            <div>
                <!-- Brand Header -->
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand">
                    <img src="{{ asset('images/hb_logo.png') }}" alt="HB Notes Admin Logo">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 800; color: #FFFFFF; line-height: 1.2;">HB Notes</span>
                        <span style="font-size: 0.725rem; color: #60A5FA; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">Admin Portal</span>
                    </div>
                </a>

                <!-- Reusable Navigation -->
                @include('layouts.partials.admin-nav')
            </div>

            <!-- Sidebar Bottom Info -->
            <div style="padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.08); font-size: 0.775rem; color: #64748B;">
                <p>HB Notes Backend v1.2</p>
                <p>Strictly Authorized Personnel</p>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="admin-main-wrapper">
            <!-- Desktop Topbar Header (>= 992px) -->
            <header class="admin-topbar d-none d-lg-flex">
                <div class="admin-topbar-title">@yield('page-title', 'Overview')</div>

                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <!-- Administrator Identity Badge -->
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div class="user-avatar-circle" style="background: var(--color-navy-700); font-weight: 700;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 0.875rem; font-weight: 600; color: var(--color-navy-900);">{{ Auth::user()->name }}</span>
                            <span style="font-size: 0.725rem; color: var(--color-text-muted);">{{ Auth::user()->email }}</span>
                        </div>
                    </div>

                    <!-- Logout Button (CSRF Protected POST) -->
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-secondary" style="height: 38px; padding: 0 1rem; font-size: 0.825rem;" id="admin-logout-btn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Body -->
            <main class="admin-content-area">
                <!-- Mobile Page Title Breadcrumb (< 992px) -->
                <div class="d-lg-none mb-3">
                    <h1 class="admin-mobile-heading">@yield('page-title', 'Overview')</h1>
                </div>

                <!-- Session Alerts -->
                @if (session('status'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                        <svg class="alert-icon flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <svg class="alert-icon flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <svg class="alert-icon flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div class="m-0">
                            @foreach ($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Local Bootstrap Bundle JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Custom Portal Script -->
    <script src="{{ asset('js/auth.js') }}"></script>
    @stack('scripts')
</body>
</html>
