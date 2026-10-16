<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\MagicLoginToken;
use App\Models\User;
use App\Notifications\AdminMagicLoginNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MagicLoginController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming magic login link request.
     */
    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $throttleKey = 'magic-login:' . Str::transliterate($email . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $user = User::where('email', $email)->first();

        // Strictly verify administrator authorization and active status
        if ($user && $user->isAdmin() && $user->isActive()) {
            $plainToken = Str::random(64);
            $hash = hash('sha256', $plainToken);

            // Invalidate previously issued outstanding unused tokens
            MagicLoginToken::where('user_id', $user->id)
                ->whereNull('used_at')
                ->delete();

            // Store single-use token hash with 10-minute expiry
            MagicLoginToken::create([
                'user_id' => $user->id,
                'token_hash' => $hash,
                'expires_at' => now()->addMinutes(10),
                'ip_address' => $request->ip(),
            ]);

            try {
                $user->notify(new AdminMagicLoginNotification($plainToken, 10));
                AdminAuditLog::log('magic_link_sent', "Magic sign-in link dispatched to {$user->email}.", $user->id, $request->ip());
            } catch (\Throwable $e) {
                Log::error('Failed sending admin magic login email: ' . $e->getMessage());
            }
        }

        // Return a generic response that does not disclose whether the email exists, is an admin, or is active
        return back()->with('status', 'If your email is eligible, you will receive a login link shortly.');
    }

    /**
     * Display the magic link confirmation screen (GET request - does NOT authenticate).
     */
    public function showConfirmation(Request $request, string $token): View
    {
        $hash = hash('sha256', $token);
        $record = MagicLoginToken::with('user')->where('token_hash', $hash)->first();

        if (! $record || ! $record->isValid() || ! $record->user || ! $record->user->isAdmin() || ! $record->user->isActive()) {
            $reason = 'Invalid Link';
            if ($record && $record->isUsed()) {
                $reason = 'This sign-in link has already been used.';
            } elseif ($record && $record->isExpired()) {
                $reason = 'This sign-in link has expired. Links are valid for 10 minutes.';
            } elseif ($record && (! $record->user || ! $record->user->isAdmin() || ! $record->user->isActive())) {
                $reason = 'The associated account is not an active administrator.';
            }

            return view('auth.magic-invalid', [
                'reason' => $reason,
            ]);
        }

        return view('auth.magic-verify', [
            'token' => $token,
            'admin' => $record->user,
        ]);
    }

    /**
     * Authenticate the administrator via POST request to prevent automated crawler logins.
     */
    public function verify(Request $request, string $token): RedirectResponse
    {
        return DB::transaction(function () use ($request, $token) {
            $hash = hash('sha256', $token);
            $record = MagicLoginToken::where('token_hash', $hash)->lockForUpdate()->first();

            if (! $record || ! $record->isValid()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'This sign-in link has expired or has already been used. Please request a new one.',
                ]);
            }

            $user = $record->user;

            if (! $user || ! $user->isAdmin() || ! $user->isActive()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'You do not have active administrator authorization for HB Notes.',
                ]);
            }

            // Atomically mark token as used
            $record->update(['used_at' => now()]);
            $user->forceFill(['last_login_at' => now()])->save();

            Auth::login($user);
            $request->session()->regenerate();

            AdminAuditLog::log('admin_login', "Administrator {$user->name} ({$user->email}) signed in via passwordless link.", $user->id, $request->ip());

            return redirect()->intended(route('admin.dashboard'))->with('status', 'Welcome back, ' . $user->name . '!');
        });
    }

    /**
     * Destroy an authenticated administrator session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AdminAuditLog::log('admin_logout', "Administrator {$user->name} signed out.", $user->id, $request->ip());
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been safely signed out.');
    }
}
