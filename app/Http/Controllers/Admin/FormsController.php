<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use App\Models\FormField;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class FormsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$forms = Form::all();
        $forms = Form::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.form.index')->with('forms', $forms);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $schools = School::where('active', 1)->get(['id', 'name']);
        $categories = SchoolCategory::all('id', 'name');
        return view('admin.form.create')
            ->with('schools', $schools)
            ->with('categories', $categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        // Create form
        $form =  new Form;
        $form->title = $request->input('title');
        $form->notes = $request->input('notes');
        $form->user_id = Auth::id();
        $form->active = true;
        $form->save();

        $formfield = $request->input('field');
        foreach(array_keys($formfield) as $key) {
            $field = new FormField;
            $field->sort_id = $key;
            $field->title = $formfield[$key]['title'];
            $field->type = $formfield[$key]['type'];
            $field->listvalues = $formfield[$key]['values'] ?? '';
            $form->form_fields()->save($field);
        }

        // Έλεγχος αν οι κατηγορίες υπάρχουν και δημιουργία πίνακα
        $category_answer = explode(',', $request->get('categories'));
        $categories = array();
        foreach ($category_answer as $category) {
            if (SchoolCategory::find($category)) {
                array_push($categories, $category);
            }
        }

        foreach ($categories as $category) {
            $form->school_categories()->attach($category);
        }

        // Έλεγχος αν τα σχολεία υπάρχουν και δημιουργία πίνακα
        $school_answer = explode(',', $request->get('schools'));
        $schools = array();
        foreach ($school_answer as $school) {
            if (School::find($school)) {
                array_push($schools, $school);
            }
        }

        foreach ($schools as $school) {
            $form->schools()->attach($school);
        }

        return redirect(route('admin.form.index'))->with('success', 'Η φόρμα δημιουργήθηκε');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $form = Form::find($id);
        if ($form)
            return view('admin.form.show')->with('form', $form);
        else
            return view('home');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $form = Form::find($id);
        if ($form) {
            $schools = School::where('active', 1)->get(['id', 'name']);
            $categories = SchoolCategory::all('id', 'name');


            $school_selected_values = array();
            foreach($form->schools as $school) {
                array_push($school_selected_values, $school->id);
            }

            $category_selected_values = array();
                foreach($form->school_categories as $category) {
                    array_push($category_selected_values, $category->id);
            }

            return view('admin.form.edit')
                ->with('schools', $schools)
                ->with('categories', $categories)
                ->with('school_selected_values', implode(",", $school_selected_values))
                ->with('category_selected_values', implode(",", $category_selected_values))
                ->with('form', $form);
        }
        else
            return view('home');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        // Update form
        $form = Form::find($id);
        $form->title = $request->input('title');
        $form->notes = $request->input('notes');
        $form->user_id = Auth::id();
        $form->save();

        // Check if we should delete fields
        $formfield = $request->input('field');
        $oldfields = $form->form_fields;
        foreach($oldfields as $oldfield) {
            if (!array_key_exists($oldfield->id, $formfield)) {
                $oldfield->delete();
            }
        }

        // Update or add fields
        foreach(array_keys($formfield) as $key) {
            $field = $form->form_fields()->firstOrNew(['id' => $key]);
            $field->sort_id = $key;
            $field->title = $formfield[$key]['title'];
            $field->type = $formfield[$key]['type'];
            $field->listvalues = $formfield[$key]['values'] ?? '';
            $form->form_fields()->save($field);
        }

        // Έλεγχος αν οι κατηγορίες υπάρχουν και δημιουργία πίνακα
        $category_answer = explode(',', $request->get('categories'));
        $categories = array();
        foreach ($category_answer as $category) {
            if (SchoolCategory::find($category)) {
                array_push($categories, $category);
            }
        }

        $form->school_categories()->sync($categories);

        // Έλεγχος αν τα σχολεία υπάρχουν και δημιουργία πίνακα
        $school_answer = explode(',', $request->get('schools'));
        $schools = array();
        foreach ($school_answer as $school) {
            if (School::find($school)) {
                array_push($schools, $school);
            }
        }

        $form->schools()->sync($schools);

        return redirect(route('admin.form.index'))->with('success', 'Η φόρμα ενημερώθηκε');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $form = Form::find($id);
        $form->form_fields()->delete();
        $form->delete();

        return redirect(route('admin.form.index'))->with('success', 'Η φόρμα διαγράφηκε');
    }

    /**
     * Παρουσίαση δεδομένων φόρμας.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function formData($id)
    {
        $form = Form::find($id);
        $form->load(
                'form_fields',
                'form_fields.field_data',
                'form_fields.field_data.schools'
        );

        $dataTable = array();
        $dataTableColumns = array();
        foreach ($form->form_fields()->get() as $field) {
            array_push($dataTableColumns, $field->title);
            foreach ($field->field_data()->get() as $field_data) {
                foreach ($field_data->schools()->get() as $school) {
                    if ($field->type == 2 || $field->type == 4) {
                        $selections = json_decode($field->listvalues);
                        foreach($selections as $selection) {
                            if ($selection->id == $field_data->data) {
                                $dataTable[$school->code][$field->title] = $selection->value;
                            }
                        }
                    } elseif ($field->type == 3) {
                        $selections = json_decode($field->listvalues);
                        $data = json_decode($field_data->data);
                        $i = 0;
                        foreach($data as $item) {
                            foreach($selections as $selection) {
                                if ($selection->id == $item) {
                                    if ($i == 0) {
                                        $dataTable[$school->code][$field->title] = $selection->value;
                                    }
                                    else {
                                        $dataTable[$school->code][$field->title] .= ", ".$selection->value;
                                    }
                                }
                            }
                            $i++;
                        }

                    }
                    else {
                        $dataTable[$school->code][$field->title] = $field_data->data;
                    }
                }
            }
        }

        $schools = School::all();
        return view('admin.form.data')
            ->with('dataTable', $dataTable)
            ->with('dataTableColumns', $dataTableColumns)
            ->with('schools', $schools);
    }
}
