<?php

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
