<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class TeacherController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Teacher::class, 'teacher');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        if ($request->exists('teacher_filter')) {
            $filter = $request->input('teacher_filter');
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
     */
    public function create(): View
    {
        return view('admin.teacher.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        Teacher::create([
            ...$request->validated(),
            'active' => 1,
        ]);

        return to_route('admin.teacher.index')
            ->with('status', 'Ο εκπαιδευτικός αποθηκεύτηκε!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher): View
    {
        return view('admin.teacher.edit')->with('teacher', $teacher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $teacher->update(
            [
                'active' => (bool) $request->validated('active', false),
                ...$request->validated(),
            ]
        );

        return to_route('admin.teacher.index')
            ->with('status', 'Ο εκπαιδευτικός ενημερώθηκε!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher): RedirectResponse
    {
        $teacher->delete();

        return to_route('admin.teacher.index')
            ->with('status', 'Ο εκπαιδευτικός διαγράφηκε!');
    }
}
