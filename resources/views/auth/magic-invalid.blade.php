@extends('layouts.auth')

@section('title', 'Sign-In Link Invalid or Expired - HB Notes')

@section('content')
<div class="auth-heading-group">
    <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.65rem; background: rgba(239, 68, 68, 0.1); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; color: #DC2626; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.85rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>Link Unavailable</span>
    </div>
    <h1 class="auth-main-heading">Sign-In Link Expired</h1>
    <p class="auth-sub-heading">{{ $reason ?? 'This sign-in link has expired, already been used, or is invalid.' }}</p>
</div>

<div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: var(--radius-md); padding: 1rem 1.25rem; color: #991B1B; font-size: 0.875rem; line-height: 1.5; margin-bottom: 2rem;">
    For security reasons, HB Notes magic sign-in links are strictly single-use and expire 10 minutes after generation. Please request a fresh link to continue.
</div>

<a href="{{ route('login') }}" class="btn-primary" style="display: flex;">
    <span>Request New Sign-In Link</span>
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="5" y1="12" x2="19" y2="12"></line>
        <polyline points="12 5 19 12 12 19"></polyline>
    </svg>
</a>
@endsection
