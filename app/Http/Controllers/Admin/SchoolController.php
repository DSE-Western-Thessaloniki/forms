<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class SchoolController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(School::class, 'school');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filter = $request->input('filter');
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
     */
    public function create(): View
    {
        $categories = SchoolCategory::all();

        return view('admin.school.create')->with('categories', $categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, #[CurrentUser] User $user): RedirectResponse
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
        $category_answer = explode(',', $request->input('category'));
        $categories = [];
        foreach ($category_answer as $category) {
            if (SchoolCategory::find($category)) {
                $categories[] = $category;
            } else {
                return redirect(route('admin.school.index'))
                    ->with('status', 'Άκυρες κατηγορίες');
            }
        }

        $school = new School([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'telephone' => $request->input('telephone'),
            'code' => $request->input('code'),
            'active' => 1,
            'updated_by' => $user->id,
        ]);

        $school->save();

        foreach ($categories as $category) {
            $school->categories()->attach($category);
        }

        return to_route('admin.school.show', [$school])
            ->with('status', 'Η σχολική μονάδα αποθηκεύτηκε!');
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school): View
    {
        return view('admin.school.show', ['school' => $school]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school): View
    {
        $categories = SchoolCategory::all();
        $category_arr = [];
        foreach ($school->categories as $category) {
            $category_arr[] = $category->id;
        }

        return view('admin.school.edit', ['school' => $school])
            ->with('categories', $categories)
            ->with('category_string', implode(',', $category_arr));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school, #[CurrentUser] User $user): RedirectResponse
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
        $category_answer = explode(',', $request->input('category'));
        $categories = [];
        foreach ($category_answer as $category) {
            if (SchoolCategory::find($category)) {
                $categories[] = $category;
            } else {
                return to_route('admin.school.index')
                    ->with('status', 'Άκυρες κατηγορίες');
            }
        }

        $school->username = $request->input('username');
        $school->name = $request->input('name');
        $school->email = $request->input('email');
        $school->telephone = $request->input('telephone');
        $school->code = $request->input('code');
        $school->active = (bool) $request->input('active');
        $school->updated_by = $user->id;

        $school->categories()->sync($categories);

        $school->save();

        return to_route('admin.school.index')->with('status', 'Η σχολική μονάδα ενημερώθηκε!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school): RedirectResponse
    {
        $school->delete();

        return to_route('admin.school.index')->with('status', 'Η σχολική μονάδα διαγράφηκε!');
    }
}
