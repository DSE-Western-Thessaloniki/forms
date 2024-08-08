<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
