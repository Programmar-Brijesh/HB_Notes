<?php

use App\Http\Controllers\Admin\AcademicClassController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\InvitationAcceptanceController;
use App\Http\Controllers\Admin\McqImportController;
use App\Http\Controllers\Admin\McqQuestionController;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Auth\MagicLoginController;
use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — HB Notes Administration Portal
|--------------------------------------------------------------------------
| Public student registration has been completely removed.
| Authentication is restricted exclusively to authorized administrators
| via high-entropy, single-use, passwordless magic email links.
*/

// Root redirect
Route::get('/', function () {
    if (Auth::check() && Auth::user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Compliance & Legal Documents
Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');

// Guest Passwordless Admin Login Routes
Route::middleware('guest')->group(function () {
    // Admin Login (Email input only - no passwords, no public signup)
    Route::get('/login', [MagicLoginController::class, 'create'])->name('login');
    Route::post('/login', [MagicLoginController::class, 'sendLink'])
        ->middleware('throttle:5,1')
        ->name('login.send');

    // Magic Link Confirmation & Verification
    Route::get('/login/magic/{token}', [MagicLoginController::class, 'showConfirmation'])->name('login.magic.show');
    Route::post('/login/magic/{token}', [MagicLoginController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('login.magic.verify');

    // Administrator Invitation Acceptance Flow
    Route::get('/admin/invitations/{token}', [InvitationAcceptanceController::class, 'show'])->name('admin.invitations.show');
    Route::post('/admin/invitations/{token}', [InvitationAcceptanceController::class, 'accept'])
        ->middleware('throttle:10,1')
        ->name('admin.invitations.accept');
});

// Authenticated Logout
Route::post('/logout', [MagicLoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Protected Administrator Portal
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Academic Structure Management
    Route::resource('classes', AcademicClassController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('chapters', ChapterController::class);

    // Notes & Educational PDF Management
    Route::get('/notes/{note}/download', [NoteController::class, 'download'])->name('notes.download');
    Route::get('/notes/{note}/preview', [NoteController::class, 'preview'])->name('notes.preview');
    Route::get('/notes/{note}/thumbnail', [NoteController::class, 'thumbnail'])->name('notes.thumbnail');
    Route::resource('notes', NoteController::class);

    // MCQ Question Bank & Bulk CSV Import
    Route::get('/mcqs/import/template', [McqImportController::class, 'template'])->name('mcqs.import.template');
    Route::get('/mcqs/import/history', [McqImportController::class, 'history'])->name('mcqs.import.history');
    Route::get('/mcqs/import', [McqImportController::class, 'create'])->name('mcqs.import');
    Route::post('/mcqs/import/preview', [McqImportController::class, 'preview'])->name('mcqs.import.preview');
    Route::post('/mcqs/import/confirm', [McqImportController::class, 'confirm'])->name('mcqs.import.confirm');
    Route::post('/mcqs/import/cancel', [McqImportController::class, 'cancel'])->name('mcqs.import.cancel');
    Route::get('/mcqs/import/{import}/report', [McqImportController::class, 'report'])->name('mcqs.import.report');
    Route::get('/mcqs/import/{import}/errors-csv', [McqImportController::class, 'downloadErrors'])->name('mcqs.import.errors-csv');
    Route::get('/mcqs/lookup/subjects', [McqImportController::class, 'lookupSubjects'])->name('mcqs.lookup.subjects');
    Route::get('/mcqs/lookup/chapters', [McqImportController::class, 'lookupChapters'])->name('mcqs.lookup.chapters');
    Route::resource('mcqs', McqQuestionController::class);

    // Administrator Management Console
    Route::get('/administrators', [AdministratorController::class, 'index'])->name('administrators.index');
    Route::post('/administrators/invite', [AdministratorController::class, 'invite'])
        ->middleware('throttle:10,1')
        ->name('administrators.invite');
    Route::post('/administrators/{user}/toggle-status', [AdministratorController::class, 'toggleStatus'])
        ->name('administrators.toggle');
    Route::delete('/administrators/invitations/{invitation}', [AdministratorController::class, 'revokeInvitation'])
        ->name('administrators.invitations.revoke');
});

// Legacy /dashboard redirect for compatibility
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'admin'])->name('dashboard');
