<?php

use App\Http\Controllers\Api\V1\AcademicClassController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\ChapterController;
use App\Http\Controllers\Api\V1\McqQuestionController;
use App\Http\Controllers\Api\V1\NoteController;
use App\Http\Controllers\Api\V1\SubjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — HB Notes Mobile & Client REST API
|--------------------------------------------------------------------------
| Versioned REST API endpoints for Flutter mobile clients.
| Supports full student authentication, password management, email verification,
| published academic content browsing, and secure educational document delivery.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ==========================================
    // 1. Public Authentication Endpoints
    // ==========================================
    Route::post('/register', [AuthController::class, 'register'])
        ->name('auth.register');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('auth.login');

    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
        ->middleware('throttle:password-reset')
        ->name('auth.forgot-password');

    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:5,1')
        ->name('auth.reset-password');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->name('verification.verify');

    // ==========================================
    // 2. Protected Authenticated User Endpoints
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])
            ->name('auth.me');

        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('auth.logout');

        Route::post('/logout-all', [AuthController::class, 'logoutAll'])
            ->name('auth.logout-all');

        Route::put('/password', [AuthController::class, 'changePassword'])
            ->name('auth.change-password');

        Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });

    // ==========================================
    // 3. Educational Content Endpoints
    // ==========================================
    // Academic Classes
    Route::get('/classes', [AcademicClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/{class}', [AcademicClassController::class, 'show'])->name('classes.show');
    Route::get('/classes/{class}/subjects', [AcademicClassController::class, 'subjects'])->name('classes.subjects');

    // Subjects
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::get('/subjects/{subject}/chapters', [SubjectController::class, 'chapters'])->name('subjects.chapters');

    // Chapters
    Route::get('/chapters', [ChapterController::class, 'index'])->name('chapters.index');
    Route::get('/chapters/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');
    Route::get('/chapters/{chapter}/notes', [ChapterController::class, 'notes'])->name('chapters.notes');
    Route::get('/chapters/{chapter}/mcqs', [ChapterController::class, 'mcqs'])->name('chapters.mcqs');
    Route::post('/chapters/{chapter}/mcqs/verify', [McqQuestionController::class, 'verify'])
        ->middleware('throttle:30,1')
        ->name('chapters.mcqs.verify');

    // Notes & Secure Document Delivery
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes/{note}/preview', [NoteController::class, 'preview'])->name('notes.preview');
    Route::get('/notes/{note}/download', [NoteController::class, 'download'])->name('notes.download');
    Route::get('/notes/{note}/thumbnail', [NoteController::class, 'thumbnail'])->name('notes.thumbnail');

    // MCQ Questions & Interactive Checking
    Route::get('/mcqs', [McqQuestionController::class, 'index'])->name('mcqs.index');
    Route::get('/mcqs/{mcq}', [McqQuestionController::class, 'show'])->name('mcqs.show');
    Route::post('/mcqs/{mcq}/check', [McqQuestionController::class, 'check'])
        ->middleware('throttle:60,1')
        ->name('mcqs.check');
});
