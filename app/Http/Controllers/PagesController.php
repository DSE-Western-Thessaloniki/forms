<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PagesController extends Controller
{
    /**
     * Home page
     */
    public function index(): View
    {
        return view('pages.index');
    }
}
