<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->exists('teacher_filter')) {
            $filter = $request->get('teacher_filter');
        } else {
            $filter = $request->session()->get('teacher_filter', '');
        }
        $request->session()->put('teacher_filter', $filter);

        if ($filter) {
            $teachers = Teacher::query()
                ->where('surname', 'like', '%'.$filter.'%')
                ->orWhere('name', 'like', '%'.$filter.'%')
                ->orWhere('am', 'like', '%'.$filter.'%')
                ->orWhere('afm', 'like', '%'.$filter.'%')
                ->orderBy('surname', 'asc')
                ->orderBy('name', 'asc')
                ->paginate(15);
        } else {
            $teachers = Teacher::orderBy('surname', 'asc')
                ->orderBy('name', 'asc')
                ->paginate(15);
        }

        return view('admin.teacher.index')
            ->with('teachers', $teachers)
            ->with('filter', $filter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.teacher.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTeacherRequest $request)
    {
        Teacher::create([
            ...$request->validated(),
            'active' => 1,
        ]);

        return to_route('admin.teacher.index');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Teacher $teacher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Teacher $teacher)
    {
        //
    }

    public function show_import()
    {

    }
}
