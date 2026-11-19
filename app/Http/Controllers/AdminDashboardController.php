<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return view('pages.admin.index');
    }
}
