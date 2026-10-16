<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AdminInvitation;
use App\Models\MagicLoginToken;
use App\Models\User;
use App\Notifications\AdminInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdministratorController extends Controller
{
    /**
     * Display the administrator management console.
     */
    public function index(): View
    {
        $administrators = User::where('role', 'admin')->latest()->get();
        $pendingInvitations = AdminInvitation::with('inviter')
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();

        $auditLogs = AdminAuditLog::with('user')->latest('id')->limit(20)->get();

        return view('admin.administrators.index', compact(
            'administrators',
            'pendingInvitations',
            'auditLogs'
        ));
    }

    /**
     * Send an administrator invitation by email.
     */
    public function invite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        $existingAdmin = User::where('email', $email)->where('role', 'admin')->where('is_active', true)->first();
        if ($existingAdmin) {
            return back()->withErrors([
                'email' => 'An active administrator with this email address already exists.',
            ]);
        }

        // Revoke any previous pending invitations for this email
        AdminInvitation::where('email', $email)
            ->whereNull('accepted_at')
            ->delete();

        $plainToken = Str::random(64);
        $hash = hash('sha256', $plainToken);

        $invitation = AdminInvitation::create([
            'email' => $email,
            'name' => trim($validated['name']),
            'token_hash' => $hash,
            'invited_by' => Auth::id(),
            'expires_at' => now()->addHours(48),
        ]);

        try {
            Notification::route('mail', $email)
                ->notify(new AdminInvitationNotification($plainToken, Auth::user()->name, 48));

            AdminAuditLog::log('admin_invited', "Invitation sent to {$email} ({$validated['name']}) by " . Auth::user()->name);
        } catch (\Throwable $e) {
            Log::error('Failed sending administrator invitation: ' . $e->getMessage());
        }

        return back()->with('status', "Invitation successfully dispatched to {$email}.");
    }

    /**
     * Toggle active/inactive status of an administrator.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if (! $user->isAdmin()) {
            abort(404);
        }

        if ($user->id === Auth::id()) {
            return back()->withErrors([
                'error' => 'You cannot deactivate your own administrator account.',
            ]);
        }

        $activeAdminCount = User::where('role', 'admin')->where('is_active', true)->count();
        if ($activeAdminCount <= 1 && $user->is_active) {
            return back()->withErrors([
                'error' => 'Security constraint: Cannot deactivate the only remaining active administrator.',
            ]);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        if (! $user->is_active) {
            // Invalidate any outstanding login tokens for the deactivated admin
            MagicLoginToken::where('user_id', $user->id)->whereNull('used_at')->delete();
        }

        $action = $user->is_active ? 'admin_reactivated' : 'admin_deactivated';
        AdminAuditLog::log($action, "Administrator {$user->name} ({$user->email}) was " . ($user->is_active ? 'reactivated' : 'deactivated') . ' by ' . Auth::user()->name);

        return back()->with('status', "Administrator {$user->name} has been " . ($user->is_active ? 'reactivated' : 'deactivated') . '.');
    }

    /**
     * Revoke a pending administrator invitation.
     */
    public function revokeInvitation(AdminInvitation $invitation): RedirectResponse
    {
        if ($invitation->accepted_at) {
            return back()->withErrors(['error' => 'This invitation has already been accepted and cannot be revoked.']);
        }

        $email = $invitation->email;
        $invitation->delete();

        AdminAuditLog::log('admin_invitation_revoked', "Invitation for {$email} was revoked by " . Auth::user()->name);

        return back()->with('status', "Invitation for {$email} has been revoked.");
    }
}
