<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application home.
     */
    public function index(): View
    {
        return view('home');
    }
}
