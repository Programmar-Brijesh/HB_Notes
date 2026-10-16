@extends('layouts.auth')

@section('title', 'Verify Administrator Sign In - HB Notes')

@section('content')
<div class="auth-heading-group">
    <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.65rem; background: rgba(16, 185, 129, 0.1); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.85rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span>Link Validated</span>
    </div>
    <h1 class="auth-main-heading">Confirm Sign In</h1>
    <p class="auth-sub-heading">You are signing into the authorized administration portal as:</p>
</div>

<!-- Administrator Identity Details Card -->
<div style="background: #F8FAFC; border: 1.5px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.25rem; margin-bottom: 1.75rem; display: flex; align-items: center; gap: 1rem;">
    <div class="user-avatar-circle" style="width: 44px; height: 44px; font-size: 1.1rem; background: #0B192C;">
        {{ strtoupper(substr($admin->name, 0, 1)) }}
    </div>
    <div>
        <div style="font-weight: 700; font-size: 1.05rem; color: var(--color-navy-900);">{{ $admin->name }}</div>
        <div style="font-size: 0.875rem; color: var(--color-text-muted);">{{ $admin->email }}</div>
        <div style="margin-top: 0.25rem;">
            <span class="status-pill active" style="font-size: 0.7rem;">Authorized Administrator</span>
        </div>
    </div>
</div>

<p style="font-size: 0.85rem; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 1.5rem;">
    To protect administrative sessions against automated mail security filters, please click below to complete authentication.
</p>

<!-- Confirmation Form (POST with CSRF) -->
<form method="POST" action="{{ route('login.magic.verify', $token) }}" data-auth-form>
    @csrf

    <button type="submit" class="btn-primary" id="confirm-login-btn">
        <span class="spinner" aria-hidden="true"></span>
        <span class="btn-text">Confirm &amp; Enter Dashboard</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
    </button>
</form>

<div class="auth-switch-box" style="margin-top: 1.5rem;">
    Not your account? <a href="{{ route('login') }}">Back to login</a>
</div>
@endsection
