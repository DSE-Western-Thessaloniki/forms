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
        return view('pages.index');
    }

    public function login() {
        return view('pages.login');
    }

    public function checkLogin(Request $request) {
        // Αν στάλθηκε αναγνωριστικό
        if ($request->input('school_id')) {
            $request->session()->put('school_id', $request->input('school_id'));
            return redirect(route('reports.index'));
        }

        // Αλλιώς πήγαινε πάλι στο login
        return redirect('pages.login');
    }

    public function logout() {
        session()->forget('school_id');
        return redirect('pages.index');
    }
}
