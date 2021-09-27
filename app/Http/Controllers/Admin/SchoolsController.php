<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\SchoolCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class SchoolsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schools = School::orderBy('created_at', 'desc')
            ->with('user', 'categories')
            ->paginate(10);
        return view('admin.school.index')->with('schools', $schools);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = SchoolCategory::all();
        return view('admin.school.create')->with('categories', $categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:schools'],
            'username' => ['required', 'string', 'max:255', 'unique:schools'],
            'code' => ['required', 'string', 'min:7', 'max:255', 'unique:schools'],
            'category' => ['required', 'string', 'max:255'],
        ]);

        // Έλεγχος αν οι κατηγορίες υπάρχουν
        $category_answer = explode(',', $request->get('category'));
        $categories = array();
        foreach ($category_answer as $category) {
            if (SchoolCategory::find($category)) {
                array_push($categories, $category);
            }
            else {
                return redirect(route('admin.school.index'))
                    ->with('status', 'Άκυρες κατηγορίες');
            }
        }

        $school = new School([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'username' => $request->get('username'),
            'code' => $request->get('code'),
            'active' => 1,
            'updated_by' => Auth::user()->id,
        ]);

        $school->save();

        foreach ($categories as $category) {
            $school->categories()->attach($category);
        }

        return redirect(route('admin.school.show', [$school]))
            ->with('status', 'Η σχολική μονάδα αποθηκεύτηκε!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\Http\Response
     */
    public function show(School $school)
    {
        return view('admin.school.show', compact('school'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\Http\Response
     */
    public function edit(School $school)
    {
        $categories = SchoolCategory::all();
        $category_arr = array();
        foreach ($school->categories as $category) {
            array_push($category_arr, $category->id);
        }
        return view('admin.school.edit', compact('school'))
            ->with('categories', $categories)
            ->with('category_string', implode(',', $category_arr));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\School  $school
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, School $school)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('schools')->ignore($school)],
            'username' => ['required', 'string', 'max:255', Rule::unique('schools')->ignore($school)],
            'code' => ['required', 'string', 'min:7', 'max:255', Rule::unique('schools')->ignore($school)],
            'category' => ['required', 'string', 'max:255'],
        ]);

        // Έλεγχος αν οι κατηγορίες υπάρχουν
        $category_answer = explode(',', $request->get('category'));
        $categories = array();
        foreach ($category_answer as $category) {
            if (SchoolCategory::find($category)) {
                array_push($categories, $category);
            }
            else {
                return redirect(route('admin.school.index'))
                    ->with('status', 'Άκυρες κατηγορίες');
            }
        }

        $school->username = $request->get('username');
        $school->name = $request->get('name');
        $school->email = $request->get('email');
        $school->code = $request->get('code');
        $school->active = $request->get('active') == 1 ? 1 : 0;
        $school->updated_by = Auth::user()->id;

        $school->categories()->sync($categories);

        $school->save();

        return redirect(route('admin.school.index'))->with('status', 'Η σχολική μονάδα ενημερώθηκε!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\Http\Response
     */
    public function destroy(School $school)
    {
        $school->delete();

        return redirect(route('admin.school.index'))->with('status', 'Η σχολική μονάδα διαγράφηκε!');
    }
}
