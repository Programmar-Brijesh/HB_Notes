<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AdminInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitationAcceptanceController extends Controller
{
    /**
     * Display the invitation acceptance view (GET - does not authenticate).
     */
    public function show(string $token): View
    {
        $hash = hash('sha256', $token);
        $invitation = AdminInvitation::with('inviter')->where('token_hash', $hash)->first();

        if (! $invitation || ! $invitation->isValid()) {
            return view('admin.invitations.invalid', [
                'reason' => ! $invitation ? 'Invalid Invitation Link' : ($invitation->isAccepted() ? 'This invitation has already been accepted.' : 'This invitation link has expired.'),
            ]);
        }

        return view('admin.invitations.accept', [
            'token' => $token,
            'invitation' => $invitation,
        ]);
    }

    /**
     * Atomically accept the administrator invitation and provision account.
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        return DB::transaction(function () use ($request, $token) {
            $hash = hash('sha256', $token);
            $invitation = AdminInvitation::where('token_hash', $hash)->lockForUpdate()->first();

            if (! $invitation || ! $invitation->isValid()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'This invitation has expired or has already been accepted.',
                ]);
            }

            // Mark invitation accepted
            $invitation->update(['accepted_at' => now()]);

            $email = strtolower(trim($invitation->email));

            // Create or update administrator user
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->forceFill([
                    'role' => 'admin',
                    'is_active' => true,
                    'last_login_at' => now(),
                ])->save();
            } else {
                $user = new User([
                    'name' => $invitation->name ?: 'Administrator',
                    'email' => $email,
                    'password' => Str::random(32),
                ]);
                $user->forceFill([
                    'role' => 'admin',
                    'is_active' => true,
                    'last_login_at' => now(),
                ])->save();
            }

            Auth::login($user);
            $request->session()->regenerate();

            AdminAuditLog::log('admin_invitation_accepted', "Administrator {$user->name} ({$user->email}) accepted invitation and activated account.", $user->id, $request->ip());

            return redirect()->route('admin.dashboard')->with('status', 'Welcome to HB Notes Administration, ' . $user->name . '!');
        });
    }
}
