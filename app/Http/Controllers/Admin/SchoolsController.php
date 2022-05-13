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
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(School::class, 'school');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter');
        if ($filter) {
            $schools = School::orderBy('created_at', 'desc')
                ->where('name', 'like', '%'.$filter.'%')
                ->orWhere('code', 'like', '%'.$filter.'%')
                ->orWhere('username', 'like', '%'.$filter.'%')
                ->with('user', 'categories')
                ->paginate(15);
        }
        else {
            $schools = School::orderBy('created_at', 'desc')
                ->with('user', 'categories')
                ->paginate(15);
        }
        return view('admin.school.index')
            ->with('schools', $schools)
            ->with('filter', $filter);
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
            'telephone' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:schools'],
            'code' => ['required', 'string', 'min:6', 'max:255', 'unique:schools'],
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
            'telephone' => $request->get('telephone'),
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
            'telephone' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('schools')->ignore($school)],
            'code' => ['required', 'string', 'min:6', 'max:255', Rule::unique('schools')->ignore($school)],
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
        $school->telephone = $request->get('telephone');
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\Support\Facades\View
     */
    public function showImport()
    {
        return view('admin.school.import');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $request->validate([
            'csvfile' => 'required|file|mimes:csv,txt',
        ]);

        $uploadedFile = $request->file('csvfile');
        $data = [];
        if (($handle = fopen($uploadedFile->getPathname(), "r")) !== FALSE) {
            while (($row_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                array_push($data, $row_data);
            }
            fclose($handle);
        }

        if (!empty($data) && count($data[0]) != 6) { // Δοκίμασε το ';' ως διαχωριστικό
            $data = [];
            if (($handle = fopen($uploadedFile->getPathname(), "r")) !== FALSE) {
                while (($row_data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    array_push($data, $row_data);
                }
                fclose($handle);
            }
        }

        if (empty($data)) {
            return redirect(route('admin.school.index'))->with('error', 'Λανθασμένη μορφή αρχείου');
        }

        foreach ($data as $row) {
            if (count($row) != 6) {
                return redirect(route('admin.school.index'))->with('error', 'Λάθος αριθμός στηλών στο αρχείο');
            }

            $school = new School;
            $school->name = $row[0];
            $school->username = $row[1];
            $school->code = $row[2];
            $school->email = $row[3];
            $school->telephone = $row[4];
            $school->active = true;
            $school->updated_by = Auth::user()->id;
            $school->save();

            $category_name = $row[5];
            $category = SchoolCategory::where('name', $category_name)->first();

            if (!$category) { // Η κατηγορία δεν υπάρχει ήδη
                $category = new SchoolCategory;
                $category->name = $row[5];
                $category->save();
            }

            $school->categories()->attach($category);

        }

        return redirect(route('admin.school.index'))->with('success', 'Έγινε εισαγωγή '.count($data).' σχολικών μονάδων');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\Support\Facades\View
     */
    public function confirmDelete(School $school)
    {
        return view('admin.school.confirm_delete')
            ->with('school', $school);
    }

}
