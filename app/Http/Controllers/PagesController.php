<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * Home page
     *
     * @return view
     */
    public function index()
    {
        $title = "Index page";
        return view('pages.index')->with('title', $title);
    }
}
