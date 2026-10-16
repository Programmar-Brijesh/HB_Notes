@extends('layouts.auth')

@section('title', 'Admin Sign In - HB Notes')

@section('content')
<div class="auth-heading-group">
    <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.65rem; background: rgba(37, 99, 235, 0.1); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.85rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        <span>Passwordless Portal</span>
    </div>
    <h1 class="auth-main-heading">Admin Sign In</h1>
    <p class="auth-sub-heading">Enter your authorized email address to receive a secure sign-in link.</p>
</div>

<form method="POST" action="{{ route('login.send') }}" data-auth-form novalidate>
    @csrf

    <!-- Administrator Email Input -->
    <div class="form-group">
        <label for="email" class="form-label">Authorized Administrator Email <span class="required">*</span></label>
        <div class="input-wrapper">
            <span class="input-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </span>
            <input
                type="email"
                id="email"
                name="email"
                class="form-input @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                autofocus
                placeholder="admin@hbnotes.com"
                aria-describedby="@error('email') email-error @enderror"
            >
        </div>
        @error('email')
            <div class="invalid-feedback" id="email-error" role="alert">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ $message }}</span>
            </div>
        @enderror
    </div>

    <!-- Security Information Box -->
    <div style="background: #F8FAFC; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <p style="font-size: 0.8rem; color: var(--color-text-muted); line-height: 1.45; margin: 0;">
            Authentication is single-use and passwordless. If your email is eligible, a time-limited sign-in link will be delivered directly to your inbox.
        </p>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn-primary" id="login-submit-btn">
        <span class="spinner" aria-hidden="true"></span>
        <span class="btn-text">Send Secure Login Link</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
    </button>
</form>
@endsection
