<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Privacy Policy - HB Notes</title>
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
                <h1 class="h2 fw-bold text-dark mb-1">Privacy Policy</h1>
                <p class="text-muted small mb-0">Last revised: October 2026</p>
            </header>

            <section class="legal-content">
                <h2>1. Information We Collect</h2>
                <p>
                    HB Notes collects only essential information required for administrator identification: full name and authorized email address. We do not solicit or store unnecessary personal data such as phone numbers, dates of birth, or home addresses.
                </p>

                <h2>2. Passwordless Authentication &amp; Token Security</h2>
                <p>
                    All administrative authentication is conducted via passwordless, single-use, high-entropy cryptographic tokens hashed using SHA-256 before storage. We never store or transmit account passwords.
                </p>

                <h2>3. How We Use Information</h2>
                <p>
                    Collected details are utilized exclusively to:
                </p>
                <ul style="padding-left: 1.5rem; color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1rem;">
                    <li>Authenticate authorized administrators and protect curriculum management tools.</li>
                    <li>Audit security operations, active sessions, and invitation activations.</li>
                    <li>Deliver single-use magic login links directly to your verified inbox.</li>
                </ul>

                <h2>4. Data Sharing &amp; Third Parties</h2>
                <p>
                    HB Notes does not sell, rent, or trade administrator or user personal information to advertisers or external commercial third parties under any circumstances.
                </p>

                <h2>5. Cookies &amp; Local Sessions</h2>
                <p>
                    We use secure HTTP-only cookies and CSRF protection tokens strictly for session management and user authentication security with a 120-minute inactivity expiration.
                </p>
            </section>
        </main>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
