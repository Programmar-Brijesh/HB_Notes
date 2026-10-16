<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Terms of Service - HB Notes</title>
    <link rel="icon" type="image/png" href="{{ asset('images/hb_logo.png') }}">

    <!-- Local Bootstrap 5.3 Framework -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body style="background-color: var(--color-surface-bg);">
    <div class="container-md py-4 px-3 px-sm-4" style="max-width: 820px;">
        <div class="mb-3">
            <a href="{{ route('login') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none fw-semibold" style="font-size: 0.875rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Return to Sign In</span>
            </a>
        </div>

        <main class="legal-container p-3 p-sm-4 p-md-5 my-0">
            <header class="legal-header mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ asset('images/hb_logo.png') }}" alt="HB Notes" style="height: 36px; width: auto; object-fit: contain;">
                    <span style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--color-navy-900);">HB Notes</span>
                </div>
                <h1 class="h2 fw-bold text-dark mb-1">Terms of Service</h1>
                <p class="text-muted small mb-0">Last revised: October 2026</p>
            </header>

            <section class="legal-content">
                <h2>1. Educational Purpose &amp; Platform Scope</h2>
                <p>
                    HB Notes provides study guides, chapter notes, formulas, and mock assessments designed specifically for secondary and senior secondary students (Classes 9 through 12). All materials are intended strictly for personal academic enrichment and test preparation.
                </p>

                <h2>2. User Accounts &amp; Security</h2>
                <p>
                    When accessing HB Notes administration or study resources, you agree to safeguard administrative credentials. Administrative sessions are passwordless and use single-use cryptographic tokens.
                </p>

                <h2>3. Intellectual Property Rights</h2>
                <p>
                    All curated notes, diagrams, practice question sets, and platform design elements are protected by intellectual property laws. Users may download or view notes for personal study but may not redistribute, resell, or republish content without authorization.
                </p>

                <h2>4. Code of Conduct</h2>
                <p>
                    Authorized users agree not to misuse platform resources, attempt unauthorized system access, bypass rate limiters, or engage in automated scraping of educational assets.
                </p>

                <h2>5. Service Availability &amp; Modifications</h2>
                <p>
                    We continuously refine and update subject notes in accordance with CBSE and state board syllabi. HB Notes reserves the right to enhance or adjust platform offerings to improve educational outcomes.
                </p>
            </section>
        </main>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
