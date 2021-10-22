<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\School;

class PagesController extends Controller
{
    /**
     * Home page
     *
     * @return view
     */
    public function index()
    {
        return view('pages.index');
    }
}
