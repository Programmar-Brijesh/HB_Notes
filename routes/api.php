<?php

use App\Http\Controllers\Api\V1\AcademicClassController;
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
| All responses use consistent JSON payloads and enforce published-only
| content, parent-child hierarchy validation, and secure PDF delivery.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // 1. Academic Classes
    Route::get('/classes', [AcademicClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/{class}', [AcademicClassController::class, 'show'])->name('classes.show');
    Route::get('/classes/{class}/subjects', [AcademicClassController::class, 'subjects'])->name('classes.subjects');

    // 2. Subjects
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::get('/subjects/{subject}/chapters', [SubjectController::class, 'chapters'])->name('subjects.chapters');

    // 3. Chapters
    Route::get('/chapters', [ChapterController::class, 'index'])->name('chapters.index');
    Route::get('/chapters/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');
    Route::get('/chapters/{chapter}/notes', [ChapterController::class, 'notes'])->name('chapters.notes');
    Route::get('/chapters/{chapter}/mcqs', [ChapterController::class, 'mcqs'])->name('chapters.mcqs');
    Route::post('/chapters/{chapter}/mcqs/verify', [McqQuestionController::class, 'verify'])
        ->middleware('throttle:30,1')
        ->name('chapters.mcqs.verify');

    // 4. Notes & Secure Document Delivery
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes/{note}/preview', [NoteController::class, 'preview'])->name('notes.preview');
    Route::get('/notes/{note}/download', [NoteController::class, 'download'])->name('notes.download');
    Route::get('/notes/{note}/thumbnail', [NoteController::class, 'thumbnail'])->name('notes.thumbnail');

    // 5. MCQ Question Bank & Interactive Checking
    Route::get('/mcqs', [McqQuestionController::class, 'index'])->name('mcqs.index');
    Route::get('/mcqs/{mcq}', [McqQuestionController::class, 'show'])->name('mcqs.show');
    Route::post('/mcqs/{mcq}/check', [McqQuestionController::class, 'check'])
        ->middleware('throttle:60,1')
        ->name('mcqs.check');
});
