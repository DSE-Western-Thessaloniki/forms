<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SchoolCategoriesController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(SchoolCategory::class, 'schoolcategory');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories = SchoolCategory::with('schools')->get();

        return view('admin.school.schoolcategory.index')->with('categories', $categories);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.school.schoolcategory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'schools' => ['sometimes', 'string', 'nullable', 'max:4096'],
        ]);

        $schoolCategory = new SchoolCategory([
            'name' => $request->input('name'),
        ]);

        $schoolCategory->save();

        $school_codes = $request->input('schools');
        if ($school_codes) {
            $codes = explode(',', $school_codes);
            foreach ($codes as $code) {
                $school = School::where('code', trim($code))->first();
                if ($school) {
                    $schoolCategory->schools()->attach($school);
                }
            }
        }

        return to_route('admin.school.schoolcategory.index')
            ->with('status', 'Η κατηγορία σχολικής μονάδας αποθηκεύτηκε!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolCategory $schoolcategory): View
    {
        return view('admin.school.schoolcategory.show', ['schoolcategory' => $schoolcategory]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolCategory $schoolcategory): View
    {
        return view('admin.school.schoolcategory.edit', ['schoolcategory' => $schoolcategory]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SchoolCategory $schoolcategory): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $schoolcategory->name = $request->input('name');

        $schoolcategory->save();

        return to_route('admin.school.schoolcategory.index')->with('status', 'Η κατηγορία σχολικής μονάδας ενημερώθηκε!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolCategory $schoolcategory): RedirectResponse
    {
        $schoolcategory->delete();

        return to_route('admin.school.schoolcategory.index')->with('status', 'Η κατηγορία σχολικής μονάδας διαγράφηκε!');
    }
}
