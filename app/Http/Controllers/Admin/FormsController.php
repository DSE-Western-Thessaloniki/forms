<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\UsesFileFiltering;
use App\Models\AcceptedFiletype;
use App\Models\Form;
use App\Models\FormField;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\SelectionList;
use App\Services\FormService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class FormsController extends Controller
{
    use UsesFileFiltering;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Form::class, 'form');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Κράτησε τις ρυθμίσεις για το φίλτρο και τις ενεργές φόρμες
        $only_active = (bool) ($request->input('only_active') ?? $request->session()->get('only_active', false));
        $request->session()->put('only_active', $only_active);

        if ($request->exists('back')) {
            $request->merge(['page' => $request->session()->get('page', 1)]);
        } else {
            $request->session()->put('page', $request->input('page', 1));
        }

        if ($request->exists('filter')) {
            $filter = $request->input('filter');
        } else {
            $filter = $request->session()->get('filter', '');
        }
        $request->session()->put('filter', $filter);

        $forms = Form::fetchWithPagination($filter, $only_active);

        return view('admin.form.index')
            ->with('forms', $forms)
            ->with('filter', $filter)
            ->with('only_active', $only_active);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $schools = School::where('active', 1)->get(['id', 'name']);
        $categories = SchoolCategory::all('id', 'name');
        $selection_lists = SelectionList::where('active', true)->get(['id', 'name']);
        $accepted_filetypes = AcceptedFiletype::all();

        return view('admin.form.create')
            ->with('schools', $schools)
            ->with('categories', $categories)
            ->with('selection_lists', $selection_lists)
            ->with('accepted_filetypes', $accepted_filetypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, FormService $formService): RedirectResponse
    {
        $this->validate($request, [
            'title' => 'required',
            'field' => 'required|array|min:1',
            'field.*.title' => 'required|string',
            'field.*.type' => 'required|integer',
            'field.*.sort_id' => 'nullable|integer|min:0',
            'field.*.required' => 'required|in:true,false,1,0',
        ]);

        DB::beginTransaction();

        $form = Form::fromRequest($request);

        FormField::fromRequest($request, $form);

        // Έλεγχος αν οι κατηγορίες υπάρχουν και δημιουργία πίνακα
        $categories = SchoolCategory::whereIn(
            'id',
            explode(',', $request->input('categories', ''))
        )->get();

        foreach ($categories as $category) {
            $form->school_categories()->attach($category);
        }

        // Έλεγχος αν τα σχολεία υπάρχουν και δημιουργία πίνακα
        $schools = School::whereIn(
            'id',
            explode(',', $request->input('schools', ''))
        )->get();

        foreach ($schools as $school) {
            $form->schools()->attach($school);
        }

        $formService->fixFormFieldOptionsAfterStore($form);

        DB::commit();

        return to_route('admin.form.index')->with('status', 'Η φόρμα δημιουργήθηκε');
    }

    /**
     * Display the specified resource.
     */
    public function show(Form $form): View
    {
        return view('admin.form.show')->with('form', $form);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Form $form): View
    {
        $schools = School::get(['id', 'name', 'active']);
        $categories = SchoolCategory::all('id', 'name');
        $accepted_filetypes = AcceptedFiletype::all();

        $school_selected_values = [];
        foreach ($form->schools as $school) {
            $school_selected_values[] = $school->id;
        }

        $category_selected_values = [];
        foreach ($form->school_categories as $category) {
            $category_selected_values[] = $category->id;
        }

        $selection_lists = SelectionList::where('active', true)->get(['id', 'name']);

        return view('admin.form.edit')
            ->with('schools', $schools)
            ->with('categories', $categories)
            ->with('school_selected_values', implode(',', $school_selected_values))
            ->with('category_selected_values', implode(',', $category_selected_values))
            ->with('form', $form)
            ->with('selection_lists', $selection_lists)
            ->with('accepted_filetypes', $accepted_filetypes);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Form $form, FormService $formService): RedirectResponse
    {
        $this->validate($request, [
            'title' => 'required',
            'field' => 'required|array|min:1',
            'field.*.title' => 'required|string',
            'field.*.type' => 'required|integer',
            'field.*.sort_id' => 'nullable|integer|min:0',
            'field.*.required' => 'required|in:true,false,1,0',
        ]);

        DB::beginTransaction();

        $form->updateFromRequest($request);

        // Check if we should delete fields
        $formField = $request->input('field');
        $oldFields = $form->form_fields;
        foreach ($oldFields as $oldField) {
            if (! array_key_exists($oldField->id, $formField)) {
                $oldField->delete();
            }
        }

        // Update or add fields
        FormField::updateFromRequest($request, $form);

        // Έλεγχος αν οι κατηγορίες υπάρχουν και δημιουργία πίνακα
        $categories = SchoolCategory::whereIn(
            'id',
            explode(',', $request->input('categories', ''))
        )->get();

        $form->school_categories()->sync($categories);

        // Έλεγχος αν τα σχολεία υπάρχουν και δημιουργία πίνακα
        $schools = School::whereIn(
            'id',
            explode(',', $request->input('schools', '') ?? '')
        )->get();

        $form->schools()->sync($schools);

        $formService->fixFormFieldOptionsAfterUpdate($form);

        DB::commit();

        return to_route('admin.form.index')->with('status', 'Η φόρμα ενημερώθηκε');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Form $form): RedirectResponse
    {
        $form->form_fields()->delete();
        $form->delete();

        return to_route('admin.form.index')->with('status', 'Η φόρμα διαγράφηκε');
    }
}
