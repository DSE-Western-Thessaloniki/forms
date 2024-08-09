<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        } else {
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
        $categories = [];
        foreach ($category_answer as $category) {
            if (SchoolCategory::find($category)) {
                array_push($categories, $category);
            } else {
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
     * @return \Illuminate\Http\Response
     */
    public function show(School $school)
    {
        return view('admin.school.show', compact('school'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(School $school)
    {
        $categories = SchoolCategory::all();
        $category_arr = [];
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
        $categories = [];
        foreach ($category_answer as $category) {
            if (SchoolCategory::find($category)) {
                array_push($categories, $category);
            } else {
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
        DB::beginTransaction();

        $request->validate([
            'csvfile' => 'required|file|mimes:csv,txt',
        ]);

        $uploadedFile = $request->file('csvfile');
        $data = [];
        $numFields = 6; // name, username, code, email, telephone, category
        $missingField = false;
        if (($handle = fopen($uploadedFile->getPathname(), 'r')) !== false) {
            while (($row_data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row_data) != $numFields) {
                    $missingField = true;
                    break;
                }

                array_push($data, [
                    'name' => $row_data[0],
                    'username' => $row_data[1],
                    'code' => $row_data[2],
                    'email' => $row_data[3],
                    'telephone' => $row_data[4],
                    'category' => $row_data[5],
                ]);
            }
            fclose($handle);
        }

        if ($missingField || empty($data)) { // Δοκίμασε το ';' ως διαχωριστικό
            $missingField = false;
            $data = [];
            if (($handle = fopen($uploadedFile->getPathname(), 'r')) !== false) {
                while (($row_data = fgetcsv($handle, 1000, ';')) !== false) {
                    if (count($row_data) != $numFields) {
                        $missingField = true;
                        break;
                    }
                    array_push($data, [
                        'name' => $row_data[0],
                        'username' => $row_data[1],
                        'code' => $row_data[2],
                        'email' => $row_data[3],
                        'telephone' => $row_data[4],
                        'category' => $row_data[5],
                    ]);
                }
                fclose($handle);
            }
        }

        if ($missingField || empty($data)) {
            return redirect(route('admin.school.index'))->with('error', 'Λανθασμένη μορφή αρχείου');
        }

        foreach ($data as $row) {
            $school = School::where('code', $row['code'])->first();

            if ($school) {
                $school->name = $row['name'];
                $school->username = $row['username'];
                $school->email = $row['email'];
                $school->telephone = $row['telephone'];
                $school->updated_by = Auth::user()->id;
                $school->save();
            } else {
                $school = new School;
                $school->name = $row['name'];
                $school->username = $row['username'];
                $school->code = $row['code'];
                $school->email = $row['email'];
                $school->telephone = $row['telephone'];
                $school->active = true;
                $school->updated_by = Auth::user()->id;
                $school->save();

                $category_name = $row['category'];
                $category = SchoolCategory::where('name', $category_name)->first();

                if (! $category) { // Η κατηγορία δεν υπάρχει ήδη
                    $category = new SchoolCategory;
                    $category->name = $row['category'];
                    $category->save();
                }

                $school->categories()->attach($category);
            }
        }

        DB::commit();

        return redirect(route('admin.school.index'))->with('success', 'Έγινε εισαγωγή '.count($data).' σχολικών μονάδων');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Support\Facades\View
     */
    public function confirmDelete(School $school)
    {
        return view('admin.school.confirm_delete')
            ->with('school', $school);
    }
}
