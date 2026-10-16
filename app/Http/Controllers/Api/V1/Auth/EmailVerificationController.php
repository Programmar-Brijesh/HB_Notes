<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    use ApiResponse;

    /**
     * Resend verification email to authenticated user.
     */
    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email is already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->successResponse(null, 'Verification link sent to your email.');
    }

    /**
     * Verify email via signed URL.
     */
    public function verify(Request $request, int $id, string $hash): JsonResponse
    {
        // Enforce Laravel's cryptographic signature & expiration check
        if (!$request->hasValidSignature()) {
            return $this->errorResponse('Invalid or expired verification link.', 403);
        }

        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        // Prevent verifying arbitrary email addresses by verifying hash of actual email
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->errorResponse('Invalid verification hash.', 403);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->successResponse(null, 'Email verified successfully.');
    }
}
