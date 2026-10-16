@extends('layouts.auth')

@section('title', 'Accept Administrator Invitation - HB Notes')

@section('content')
<div class="auth-heading-group">
    <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.65rem; background: rgba(37, 99, 235, 0.1); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.85rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="8.5" cy="7" r="4"></circle>
            <line x1="20" y1="8" x2="20" y2="14"></line>
            <line x1="23" y1="11" x2="17" y2="11"></line>
        </svg>
        <span>Administrator Invitation</span>
    </div>
    <h1 class="auth-main-heading">Join the Admin Team</h1>
    <p class="auth-sub-heading">You have been invited to manage educational content on the HB Notes platform.</p>
</div>

<div style="background: #F8FAFC; border: 1.5px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.25rem; margin-bottom: 1.75rem;">
    <div style="margin-bottom: 0.85rem;">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; letter-spacing: 0.05em;">Invited Email</span>
        <div style="font-size: 1.05rem; font-weight: 700; color: var(--color-navy-900);">{{ $invitation->email }}</div>
    </div>
    <div style="margin-bottom: 0.85rem;">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; letter-spacing: 0.05em;">Invited By</span>
        <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-navy-700);">{{ $invitation->inviter?->name ?? 'System Administrator' }}</div>
    </div>
    <div>
        <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; letter-spacing: 0.05em;">Assigned Role</span>
        <div><span class="status-pill active" style="font-size: 0.725rem;">Administrator</span></div>
    </div>
</div>

<p style="font-size: 0.85rem; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 1.5rem;">
    Click below to activate your administrative privileges and immediately enter the HB Notes Admin Portal. Subsequent logins will use passwordless email links.
</p>

<!-- Form (POST with CSRF) -->
<form method="POST" action="{{ route('admin.invitations.accept', $token) }}" data-auth-form>
    @csrf

    <button type="submit" class="btn-primary" id="accept-invite-btn">
        <span class="spinner" aria-hidden="true"></span>
        <span class="btn-text">Activate Administrator Account</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </button>
</form>
@endsection
