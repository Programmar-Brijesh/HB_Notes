<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\AdminAuditLog;
use App\Models\AdminInvitation;
use App\Models\Chapter;
use App\Models\McqQuestion;
use App\Models\Note;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the main HB Notes admin dashboard with real database-backed metrics.
     */
    public function index(): View
    {
        $admin = Auth::user();

        // 100% Database-backed metrics
        $totalClassesCount = AcademicClass::count();
        $totalSubjectsCount = Subject::count();
        $totalChaptersCount = Chapter::count();
        $totalNotesCount = Note::count();
        $publishedNotesCount = Note::published()->count();
        $totalMcqsCount = McqQuestion::count();
        $publishedMcqsCount = McqQuestion::published()->count();
        $activeAdminCount = User::where('role', 'admin')->where('is_active', true)->count();
        $pendingInvitationCount = AdminInvitation::whereNull('accepted_at')->where('expires_at', '>', now())->count();

        $recentAuditLogs = AdminAuditLog::with('user')
            ->latest('id')
            ->limit(7)
            ->get();

        $classes = AcademicClass::withCount(['subjects', 'chapters'])
            ->orderBy('class_number')
            ->get();

        $recentSubjects = Subject::with('academicClass')
            ->withCount('chapters')
            ->latest('id')
            ->limit(5)
            ->get();

        $recentNotes = Note::with(['chapter.subject.academicClass'])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'admin',
            'totalClassesCount',
            'totalSubjectsCount',
            'totalChaptersCount',
            'totalNotesCount',
            'publishedNotesCount',
            'totalMcqsCount',
            'publishedMcqsCount',
            'activeAdminCount',
            'pendingInvitationCount',
            'recentAuditLogs',
            'classes',
            'recentSubjects',
            'recentNotes'
        ));
    }
}
