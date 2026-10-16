@extends('layouts.auth')

@section('title', 'Invitation Link Invalid - HB Notes')

@section('content')
<div class="auth-heading-group">
    <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.65rem; background: rgba(239, 68, 68, 0.1); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; color: #DC2626; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.85rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>Invitation Invalid</span>
    </div>
    <h1 class="auth-main-heading">Invitation Expired</h1>
    <p class="auth-sub-heading">{{ $reason ?? 'This invitation is no longer valid or has already been accepted.' }}</p>
</div>

<div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: var(--radius-md); padding: 1rem 1.25rem; color: #991B1B; font-size: 0.875rem; line-height: 1.5; margin-bottom: 2rem;">
    Administrator invitations are time-limited (48 hours) and single-use. If you need access, please ask an authorized administrator to issue a new invitation.
</div>

<a href="{{ route('login') }}" class="btn-primary" style="display: flex;">
    <span>Go to Admin Sign In</span>
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="5" y1="12" x2="19" y2="12"></line>
        <polyline points="12 5 19 12 12 19"></polyline>
    </svg>
</a>
@endsection
