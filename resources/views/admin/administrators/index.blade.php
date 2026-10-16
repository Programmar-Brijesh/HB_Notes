@extends('layouts.admin')

@section('title', 'Manage Administrators - HB Notes')
@section('page-title', 'Manage Administrators')

@section('content')
<!-- Invite New Administrator Card -->
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <div>
            <h3 class="admin-card-title mb-1">Invite Authorized Administrator</h3>
            <p class="text-muted small mb-0">
                Send a secure single-use invitation link valid for 48 hours. The recipient will activate their account via email.
            </p>
        </div>
    </div>

    <div class="p-3 p-sm-4">
        <form method="POST" action="{{ route('admin.administrators.invite') }}" data-auth-form>
            @csrf

            <div class="row g-3 align-items-end">
                <!-- Name -->
                <div class="col-12 col-md-5">
                    <label for="name" class="form-label small fw-semibold">Administrator Name <span class="required">*</span></label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-input no-icon"
                        placeholder="e.g. Priyanshu Sharma"
                        value="{{ old('name') }}"
                        required
                    >
                </div>

                <!-- Email -->
                <div class="col-12 col-md-5">
                    <label for="invite-email" class="form-label small fw-semibold">Administrator Email <span class="required">*</span></label>
                    <input
                        type="email"
                        name="email"
                        id="invite-email"
                        class="form-input no-icon"
                        placeholder="colleague@hbnotes.com"
                        value="{{ old('email') }}"
                        required
                    >
                </div>

                <!-- Submit Button -->
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn-primary w-100" style="height: 48px; padding: 0 1rem; white-space: nowrap;" id="send-invite-btn">
                        <span class="spinner" aria-hidden="true"></span>
                        <span class="btn-text">Send Invite</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Active Administrators Table -->
<div class="admin-card mb-4">
    <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-1">
        <h3 class="admin-card-title mb-0">Authorized Administrators ({{ $administrators->count() }})</h3>
        <span class="text-muted small">Content &amp; Curriculum Access</span>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="text-nowrap">Administrator</th>
                    <th class="text-nowrap">Email Address</th>
                    <th>Role</th>
                    <th>Account Status</th>
                    <th class="text-nowrap">Last Active</th>
                    <th style="text-align: right;" class="text-nowrap">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($administrators as $admin)
                    <tr>
                        <td style="font-weight: 600; color: var(--color-navy-900);" class="text-nowrap">
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-circle" style="width: 28px; height: 28px; font-size: 0.75rem; background: #0B192C;">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </div>
                                <span>{{ $admin->name }}</span>
                                @if ($admin->id === Auth::id())
                                    <span style="font-size: 0.7rem; color: var(--color-primary); font-weight: 700;">(You)</span>
                                @endif
                            </div>
                        </td>
                        <td style="color: var(--color-text-main);" class="text-nowrap">{{ $admin->email }}</td>
                        <td>
                            <span class="status-pill active" style="font-size: 0.725rem;">Administrator</span>
                        </td>
                        <td>
                            @if ($admin->is_active)
                                <span class="status-pill active">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10B981;"></span>
                                    <span>Active</span>
                                </span>
                            @else
                                <span class="status-pill inactive">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #EF4444;"></span>
                                    <span>Inactive</span>
                                </span>
                            @endif
                        </td>
                        <td class="text-muted small text-nowrap">
                            {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never logged in' }}
                        </td>
                        <td style="text-align: right;" class="text-nowrap">
                            @if ($admin->id !== Auth::id())
                                <form method="POST" action="{{ route('admin.administrators.toggle', $admin) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to {{ $admin->is_active ? 'deactivate' : 'reactivate' }} {{ $admin->name }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $admin->is_active ? 'danger' : 'success' }} px-3 py-1" style="font-size: 0.8rem; font-weight: 600;">
                                        {{ $admin->is_active ? 'Deactivate' : 'Reactivate' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">Current session</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Pending Invitations Table -->
@if ($pendingInvitations->isNotEmpty())
    <div class="admin-card mb-4">
        <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-1">
            <h3 class="admin-card-title mb-0">Pending Administrator Invitations ({{ $pendingInvitations->count() }})</h3>
            <span class="text-muted small">Awaiting recipient acceptance</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="text-nowrap">Recipient</th>
                        <th class="text-nowrap">Invited By</th>
                        <th class="text-nowrap">Sent Date</th>
                        <th class="text-nowrap">Expires In</th>
                        <th style="text-align: right;" class="text-nowrap">Revoke</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingInvitations as $invite)
                        <tr>
                            <td class="text-nowrap">
                                <div style="font-weight: 600; color: var(--color-navy-900);">{{ $invite->name ?: 'Administrator' }}</div>
                                <div class="text-muted small">{{ $invite->email }}</div>
                            </td>
                            <td style="color: var(--color-text-main);" class="text-nowrap">
                                {{ $invite->inviter?->name ?? 'System Administrator' }}
                            </td>
                            <td class="text-muted small text-nowrap">
                                {{ $invite->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="text-nowrap">
                                <span class="status-pill pending">
                                    {{ $invite->expires_at->diffForHumans() }}
                                </span>
                            </td>
                            <td style="text-align: right;" class="text-nowrap">
                                <form method="POST" action="{{ route('admin.administrators.invitations.revoke', $invite) }}" style="display: inline;" onsubmit="return confirm('Revoke invitation for {{ $invite->email }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-1" style="font-size: 0.8rem; font-weight: 600;">
                                        Revoke
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Administrative Security Audit Log -->
<div class="admin-card mb-4">
    <div class="admin-card-header d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-1">
        <h3 class="admin-card-title mb-0">Security &amp; Administrator Audit Trail</h3>
        <span class="text-muted small">Historical administrative logs</span>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="text-nowrap">Timestamp</th>
                    <th>Action</th>
                    <th>Administrator</th>
                    <th>Event Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($auditLogs as $log)
                    <tr>
                        <td class="text-muted small text-nowrap">
                            {{ $log->created_at->format('M d, Y H:i:s') }}
                        </td>
                        <td>
                            <span class="status-pill {{ str_contains($log->action, 'login') ? 'active' : (str_contains($log->action, 'deactivated') ? 'inactive' : 'pending') }}">
                                {{ str_replace('_', ' ', strtoupper($log->action)) }}
                            </span>
                        </td>
                        <td class="fw-semibold text-nowrap" style="color: var(--color-navy-900);">
                            {{ $log->user?->name ?? 'System' }}
                        </td>
                        <td>
                            {{ $log->description }}
                        </td>
                        <td class="font-monospace small text-muted text-nowrap">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
