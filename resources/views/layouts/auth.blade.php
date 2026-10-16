<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'HB Notes - Administration Portal')</title>
    <meta name="description" content="Authorized administration portal for managing HB Notes academic resources, syllabi, and study notes for Classes 9 to 12.">

    <link rel="icon" type="image/png" href="{{ asset('images/hb_logo.png') }}">

    <!-- Local Bootstrap 5.3 Framework -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    <!-- Custom Administrative & Authentication Styles -->
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @stack('styles')
</head>
<body class="auth-body">
    <div class="auth-split-wrapper">
        <!-- Form Side -->
        <main class="auth-form-side">
            <div class="w-100">
                <!-- Brand Header -->
                <header class="auth-brand-header">
                    <a href="{{ route('home') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                        <img src="{{ asset('images/hb_logo.png') }}" alt="HB Notes Official Logo" class="auth-brand-logo">
                        <div class="auth-brand-text">
                            <span class="auth-brand-title">HB Notes</span>
                            <span class="auth-brand-badge">Administration Portal</span>
                        </div>
                    </a>
                </header>

                <!-- Page-Specific Form Container -->
                <div class="auth-form-inner">
                    <!-- Session Status Alert -->
                    @if (session('status'))
                        <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                            <svg class="alert-icon flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <div>{{ session('status') }}</div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>

            <!-- Footer -->
            <footer class="auth-copyright-footer">
                <p>&copy; {{ date('Y') }} HB Notes. All rights reserved. &bull; <a href="{{ route('terms') }}">Terms</a> &bull; <a href="{{ route('privacy') }}">Privacy</a></p>
                <p class="auth-footer-sub">Authorized Administrator Access Only</p>
            </footer>
        </main>

        <!-- Educational Showcase Panel (Desktop >= 992px) -->
        <aside class="auth-promo-side d-none d-lg-flex" aria-label="HB Notes Overview">
            <div class="promo-top-bar">
                <div class="promo-pill-badge">
                    <span class="promo-pill-dot"></span>
                    <span>Admin Operations Active</span>
                </div>
                <div style="font-size: 0.825rem; font-weight: 500; color: #94A3B8;">
                    CBSE &amp; State Boards
                </div>
            </div>

            <div class="promo-content">
                <h2 class="promo-headline">Empowering Classes 9–12 Academic Excellence</h2>
                <p class="promo-description">
                    Curate syllabus-aligned study guides, coordinate chapter-wise revision notes, and prepare high-impact assessment resources for secondary education scholars.
                </p>

                <!-- Concise Feature Highlights -->
                <div class="promo-features">
                    <div class="promo-feature-card">
                        <div class="promo-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                        <div class="promo-feature-text">
                            <h4>Classes 9–12 Curriculum Hub</h4>
                            <p>Organize comprehensive subject structures across Physics, Chemistry, Maths &amp; Biology.</p>
                        </div>
                    </div>

                    <div class="promo-feature-card">
                        <div class="promo-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                        </div>
                        <div class="promo-feature-text">
                            <h4>Structured Content Distribution</h4>
                            <p>Manage verified chapter notes, formula sheets, and PDF study materials for upcoming mobile apps.</p>
                        </div>
                    </div>

                    <div class="promo-feature-card">
                        <div class="promo-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <div class="promo-feature-text">
                            <h4>Zero-Password Administrative Security</h4>
                            <p>Single-use, high-entropy cryptographic tokens eliminate password vulnerability and brute-force risks.</p>
                        </div>
                    </div>
                </div>

                <!-- Preview Board Illustration -->
                <div class="promo-preview-board">
                    <div class="board-header">
                        <div class="board-header-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#60A5FA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>Cryptographic Magic Link Verification</span>
                        </div>
                        <span class="board-header-tag">SHA-256</span>
                    </div>

                    <div class="board-pills-row">
                        <span class="board-pill active">Single-Use</span>
                        <span class="board-pill active">10m Expiry</span>
                        <span class="board-pill">Scanner Protected</span>
                        <span class="board-pill">Atomic Consumption</span>
                    </div>

                    <div class="board-progress-row">
                        <span>Access Protocol State</span>
                        <div class="board-progress-bar">
                            <div class="board-progress-fill" style="width: 100%; background: linear-gradient(90deg, #3B82F6 0%, #10B981 100%);"></div>
                        </div>
                        <span style="font-weight: 600; color: #10B981;">Secured</span>
                    </div>
                </div>
            </div>

            <div class="promo-footer">
                <span>HB Notes Educational Platform</span>
                <span>Content Management Core</span>
            </div>
        </aside>
    </div>

    <!-- Local Bootstrap Bundle JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Custom Auth Scripts -->
    <script src="{{ asset('js/auth.js') }}"></script>
    @stack('scripts')
</body>
</html>
