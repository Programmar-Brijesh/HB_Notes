<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    /**
     * Display the Terms of Service.
     */
    public function terms(): View
    {
        return view('legal.terms');
    }

    /**
     * Display the Privacy Policy.
     */
    public function privacy(): View
    {
        return view('legal.privacy');
    }
}
