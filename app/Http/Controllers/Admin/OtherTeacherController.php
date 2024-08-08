<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOtherTeacherRequest;
use App\Http\Requests\UpdateOtherTeacherRequest;
use App\Models\OtherTeacher;
use Illuminate\Http\Request;

class OtherTeacherController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(OtherTeacher::class, 'other_teacher');
    }

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
            $otherTeachers = OtherTeacher::query()
                ->where('employeenumber', 'like', '%'.$filter.'%')
                ->orWhere('name', 'like', '%'.$filter.'%')
                ->orWhere('email', 'like', '%'.$filter.'%')
                ->paginate(15);
        } else {
            $otherTeachers = OtherTeacher::orderBy('name', 'asc')
                ->paginate(15);
        }

        return view('admin.other_teacher.index')
            ->with('other_teachers', $otherTeachers)
            ->with('filter', $filter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOtherTeacherRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(OtherTeacher $otherTeachers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(OtherTeacher $otherTeachers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOtherTeacherRequest $request, OtherTeacher $otherTeachers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(OtherTeacher $otherTeachers)
    {
        //
    }
}
