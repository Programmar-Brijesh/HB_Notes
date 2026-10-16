<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the authenticated student dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        $classes = [
            ['id' => 9, 'title' => 'Class 9', 'desc' => 'Foundational concepts in Science & Maths', 'badge' => 'Foundation'],
            ['id' => 10, 'title' => 'Class 10', 'desc' => 'Board exam preparation & chapter revisions', 'badge' => 'Board Prep'],
            ['id' => 11, 'title' => 'Class 11', 'desc' => 'Advanced theoretical frameworks & numericals', 'badge' => 'Advanced'],
            ['id' => 12, 'title' => 'Class 12', 'desc' => 'Final board prep & competitive readiness', 'badge' => 'Senior Board'],
        ];

        $recentResources = [
            ['subject' => 'Physics', 'class' => 'Class 10', 'topic' => 'Light: Reflection and Refraction', 'pages' => 14, 'date' => 'Updated recently'],
            ['subject' => 'Chemistry', 'class' => 'Class 12', 'topic' => 'Electrochemistry & Chemical Kinetics', 'pages' => 22, 'date' => 'Updated recently'],
            ['subject' => 'Mathematics', 'class' => 'Class 11', 'topic' => 'Trigonometric Functions & Identities', 'pages' => 18, 'date' => 'Updated recently'],
            ['subject' => 'Biology', 'class' => 'Class 9', 'topic' => 'The Fundamental Unit of Life: Cell', 'pages' => 12, 'date' => 'Updated recently'],
        ];

        return view('dashboard', compact('user', 'classes', 'recentResources'));
    }
}
