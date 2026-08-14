<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     *
     * @return Response
     */
    public function index()
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
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'schools' => ['sometimes', 'string', 'nullable', 'max:4096'],
        ]);

        $schoolCategory = new SchoolCategory([
            'name' => $request->get('name'),
        ]);

        $schoolCategory->save();

        $school_codes = $request->get('schools');
        if ($school_codes) {
            $codes = explode(',', $school_codes);
            foreach ($codes as $code) {
                $school = School::where('code', trim($code))->first();
                if ($school) {
                    $schoolCategory->schools()->attach($school);
                }
            }
        }

        return redirect(route('admin.school.schoolcategory.index'))
            ->with('status', 'Η κατηγορία σχολικής μονάδας αποθηκεύτηκε!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolCategory $schoolcategory): View
    {
        return view('admin.school.schoolcategory.show', compact('schoolcategory'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  SchoolCategory  $schoolCategory
     */
    public function edit(SchoolCategory $schoolcategory): View
    {
        return view('admin.school.schoolcategory.edit', compact('schoolcategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, SchoolCategory $schoolcategory)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $schoolcategory->name = $request->get('name');

        $schoolcategory->save();

        return to_route('admin.school.schoolcategory.index')->with('status', 'Η κατηγορία σχολικής μονάδας ενημερώθηκε!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(SchoolCategory $schoolcategory)
    {
        $schoolcategory->delete();

        return to_route('admin.school.schoolcategory.index')->with('status', 'Η κατηγορία σχολικής μονάδας διαγράφηκε!');
    }
}
