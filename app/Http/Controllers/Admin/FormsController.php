<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\FormField;
use App\Models\OtherTeacher;
use App\Models\SelectionList;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use XLSXWriter;

class FormsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Form::class, 'form');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request) : \Illuminate\Contracts\View\View
    {
        // Κράτησε τις ρυθμίσεις για το φίλτρο και τις ενεργές φόρμες
        $only_active = $request->get('only_active') ?? $request->session()->get('only_active', 0);
        $request->session()->put('only_active', $only_active);
        if ($request->exists('filter')) {
            $filter = $request->get('filter');
        } else {
            $filter = $request->session()->get('filter', '');
        }
        $request->session()->put('filter', $filter);

        if ($filter && $only_active) {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount(['data' => function($query) {
                    $query->where('record', 0);
                }])
                ->where('active', '1')
                ->where(function($query) use ($filter) {
                    $query->where('id', 'like', '%'.$filter.'%')
                    ->orWhere('title', 'like', '%'.$filter.'%');
                })
                ->with('user')
                ->withCount(['schools' => function($query) {
                    $query->where('active', 1);
                }])
                ->paginate(15);
        }
        else if ($filter) {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount(['data' => function($query) {
                    $query->where('record', 0);
                }])
                ->where('id', 'like', '%'.$filter.'%')
                ->orWhere('title', 'like', '%'.$filter.'%')
                ->with('user')
                ->withCount(['schools' => function($query) {
                    $query->where('active', 1);
                }])
                ->paginate(15);
        }
        else if ($only_active) {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount(['data' => function($query) {
                    $query->where('record', 0);
                }])
                ->where('active', '1')
                ->with('user')
                ->withCount(['schools' => function($query) {
                    $query->where('active', 1);
                }])
                ->paginate(15);
        }
        else {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount([
                    'data' => function($query) {
                        $query->where('record', 0);
                    },
                ])
                ->with('user')
                ->withCount(['schools' => function($query) {
                    $query->where('active', 1);
                }])
                ->paginate(15);
        }

        return view('admin.form.index')
            ->with('forms', $forms)
            ->with('filter', $filter)
            ->with('only_active', $only_active);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create() : \Illuminate\Contracts\View\View
    {
        $schools = School::where('active', 1)->get(['id', 'name']);
        $categories = SchoolCategory::all('id', 'name');
        $selection_lists = SelectionList::where('active', true)->get(['id', 'name']);
        return view('admin.form.create')
            ->with('schools', $schools)
            ->with('categories', $categories)
            ->with('selection_lists', $selection_lists);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request) : \Illuminate\Http\RedirectResponse
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        DB::beginTransaction();

        // Create form
        $form =  new Form;
        $form->title = $request->input('title');
        $form->notes = $request->input('notes');
        $form->user_id = Auth::id();
        $form->active = true;
        $form->multiple = $request->input('multiple_input') ? true : false;
        $form->for_teachers = intval($request->input('for_teachers'));
        $form->for_all_teachers = intval($request->input('for_all_teachers'));
        $form->save();

        $formfield = $request->input('field');
        foreach(array_keys($formfield) as $key) {
            $field = new FormField;
            $field->sort_id = $formfield[$key]['sort_id'];
            $field->title = $formfield[$key]['title'];

            if ($formfield[$key]['type'] === FormField::TYPE_LIST) {
                $selection_list = SelectionList::find($formfield[$key]['selection_list']);

                $field->type = FormField::TYPE_SELECT;
                $field->listvalues = $selection_list->data;
            } else {
                $field->type = $formfield[$key]['type'];
                $field->listvalues = $formfield[$key]['values'] ?? '';
            }
            $field->required = $formfield[$key]['required'] === "true" ? true : false;
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

        DB::commit();

        return redirect(route('admin.form.index'))->with('status', 'Η φόρμα δημιουργήθηκε');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Form $form) : \Illuminate\Contracts\View\View
    {
        return view('admin.form.show')->with('form', $form);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Form $form) : \Illuminate\Contracts\View\View
    {
        $schools = School::get(['id', 'name', 'active']);
        $categories = SchoolCategory::all('id', 'name');


        $school_selected_values = array();
        foreach($form->schools as $school) {
            array_push($school_selected_values, $school->id);
        }

        $category_selected_values = array();
        foreach($form->school_categories as $category) {
            array_push($category_selected_values, $category->id);
        }

        $selection_lists = SelectionList::where('active', true)->get(['id', 'name']);

        return view('admin.form.edit')
            ->with('schools', $schools)
            ->with('categories', $categories)
            ->with('school_selected_values', implode(",", $school_selected_values))
            ->with('category_selected_values', implode(",", $category_selected_values))
            ->with('form', $form)
            ->with('selection_lists', $selection_lists);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Form $form) : \Illuminate\Http\RedirectResponse
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        DB::beginTransaction();

        // Update form
        $form->title = $request->input('title');
        $form->notes = $request->input('notes');
        $form->multiple = $request->input('multiple_input') ? true : false;
        $form->for_teachers = intval($request->input('for_teachers'));
        $form->for_all_teachers = intval($request->input('for_all_teachers'));
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
            $field->sort_id = $formfield[$key]['sort_id'] ?? $key;
            $field->title = $formfield[$key]['title'];

            if ($formfield[$key]['type'] == FormField::TYPE_LIST) {
                $selection_list = SelectionList::find($formfield[$key]['selection_list']);

                $field->type = FormField::TYPE_SELECT;
                $field->listvalues = $selection_list->data;
            } else {
                $field->type = $formfield[$key]['type'];
                $field->listvalues = $formfield[$key]['values'] ?? '';
            }

            $field->required = $formfield[$key]['required'] === "true" ? true : false;
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

        DB::commit();

        return redirect(route('admin.form.index'))->with('status', 'Η φόρμα ενημερώθηκε');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Form $form) : \Illuminate\Http\RedirectResponse
    {
        $form->form_fields()->delete();
        $form->delete();

        return redirect(route('admin.form.index'))->with('status', 'Η φόρμα διαγράφηκε');
    }

    /**
     * Παρουσίαση δεδομένων φόρμας.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Contracts\View\View
     */
    public function formData(Form $form) : \Illuminate\Contracts\View\View
    {
        $form->load(
                'form_fields',
                'form_fields.field_data',
                'form_fields.field_data.school',
                'form_fields.field_data.teacher',
                'form_fields.field_data.other_teacher'
        );

        $dataTable = array();
        $dataTableColumns = array();
        if ($form->for_teachers) {
            foreach ($form->form_fields as $field) {
                array_push($dataTableColumns, $field->title);
                foreach ($field->field_data as $field_data) {
                    if ($field_data->teacher?->active == 1 || $field_data->other_teacher) {
                        if ($field_data->teacher) {
                            $teacher_am = $field_data->teacher->am;
                        } else {
                            $teacher_am = $field_data->other_teacher->employeenumber;
                        }
                        if ($field->type == FormField::TYPE_RADIO_BUTTON || $field->type == FormField::TYPE_SELECT) {
                            $selections = json_decode($field->listvalues);
                            foreach($selections as $selection) {
                                if ($selection->id == $field_data->data) {
                                    $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                        'value' => $selection->value,
                                        'created' => $field_data->created_at,
                                        'updated' => $field_data->updated_at
                                    ];
                                }
                            }
                        } elseif ($field->type == FormField::TYPE_CHECKBOX) {
                            $selections = json_decode($field->listvalues);
                            if ($field_data->data === null) {
                                $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                    'value' => "",
                                    'created' => $field_data->created_at,
                                    'updated' => $field_data->updated_at
                                ];
                            } else {
                                $data = json_decode($field_data->data);
                                $i = 0;
                                foreach($data as $item) {
                                    foreach($selections as $selection) {
                                        if ($selection->id == $item) {
                                            if ($i == 0 || $dataTable[$teacher_am][$field->title][$field_data->record] == "") {
                                                $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                                    'value' => $selection->value,
                                                    'created' => $field_data->created_at,
                                                    'updated' => $field_data->updated_at
                                                ];
                                            }
                                            else {
                                                $dataTable[$teacher_am][$field->title][$field_data->record]['value'] .= ", ".$selection->value;
                                            }
                                        }
                                    }
                                    $i++;
                                }
                            }

                        } elseif ($field->type == FormField::TYPE_NUMBER) {
                            $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                'value' => intval($field_data->data),
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                        else {
                            $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                'value' => $field_data->data,
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                    }
                }
            }
        } else {
            foreach ($form->form_fields as $field) {
                array_push($dataTableColumns, $field->title);
                foreach ($field->field_data as $field_data) {
                    if ($field_data->school->active == 1) {
                        if ($field->type == FormField::TYPE_RADIO_BUTTON || $field->type == FormField::TYPE_SELECT) {
                            $selections = json_decode($field->listvalues);
                            foreach($selections as $selection) {
                                if ($selection->id == $field_data->data) {
                                    $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                        'value' => $selection->value,
                                        'created' => $field_data->created_at,
                                        'updated' => $field_data->updated_at
                                    ];
                                }
                            }
                        } elseif ($field->type == FormField::TYPE_CHECKBOX) {
                            $selections = json_decode($field->listvalues);
                            if ($field_data->data === null) {
                                $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                    'value' => "",
                                    'created' => $field_data->created_at,
                                    'updated' => $field_data->updated_at
                                ];
                            } else {
                                $data = json_decode($field_data->data);
                                $i = 0;
                                foreach($data as $item) {
                                    foreach($selections as $selection) {
                                        if ($selection->id == $item) {
                                            if ($i == 0 || $dataTable[$field_data->school->code][$field->title][$field_data->record] == "") {
                                                $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                                    'value' => $selection->value,
                                                    'created' => $field_data->created_at,
                                                    'updated' => $field_data->updated_at
                                                ];
                                            }
                                            else {
                                                $dataTable[$field_data->school->code][$field->title][$field_data->record]['value'] .= ", ".$selection->value;
                                            }
                                        }
                                    }
                                    $i++;
                                }
                            }

                        } elseif ($field->type == FormField::TYPE_NUMBER) {
                            $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                'value' => intval($field_data->data),
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                        else {
                            $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                'value' => $field_data->data,
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                    }
                }
            }
        }

        $schools = [];
        $teachers = [];
        if ($form->for_teachers) {
            // Βρες όλους τους καθηγητές που θα έπρεπε να απαντήσουν
            $teachers = Teacher::where('active', 1)->get()->toArray();
            if ($form->for_all_teachers) {
                $other_teachers = OtherTeacher::all()->toArray();
            }
        } else {
            // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
            $schools = $form->schools->where('active', 1);
            $categories = $form->school_categories;
            foreach($categories as $category) {
                $schools = $schools->concat($category->schools->where('active', 1));
            }
            $schools = $schools->unique('id')->toArray();
        }

        return view('admin.form.data')
            ->with('dataTable', $dataTable)
            ->with('dataTableColumns', $dataTableColumns)
            ->with('schools', array_values($schools))
            ->with('teachers', array_values($teachers))
            ->with('other_teachers', array_values($other_teachers ?? []))
            ->with('form', $form);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     *
     * @param  \App\Models\Form  $form
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function formDataCSV(Form $form) : \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $form->load(
                'form_fields',
                'form_fields.field_data',
                'form_fields.field_data.school',
                'form_fields.field_data.teacher',
                'form_fields.field_data.other_teacher'
        );

        $teacher_ids = []; // Πίνακας για να κρατήσουμε τα id των εκπαιδευτικών που απάντησαν
        $other_teacher_ids = [];
        if ($form->for_teachers) {
            $dataTableColumns = array('Εκπαιδευτικός', 'ΑΜ/ΑΦΜ');
            foreach ($form->form_fields as $field) {
                array_push($dataTableColumns, $field->title);
                foreach ($field->field_data as $field_data) {
                    if ($field_data->teacher?->active == 1 || $field_data->other_teacher) {
                        if ($field_data->teacher) {
                            array_push($teacher_ids, $field_data->teacher->id);
                            $teacher_am = $field_data->teacher->am;
                        } else {
                            array_push($other_teacher_ids, $field_data->other_teacher->id);
                            $teacher_am = $field_data->other_teacher->employeenumber;
                        }
                        if ($field->type == FormField::TYPE_RADIO_BUTTON || $field->type == FormField::TYPE_SELECT) {
                            $selections = json_decode($field->listvalues);
                            foreach($selections as $selection) {
                                if ($selection->id == $field_data->data) {
                                    $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                        'value' => $selection->value,
                                        'created' => $field_data->created_at,
                                        'updated' => $field_data->updated_at
                                    ];
                                }
                            }
                        } elseif ($field->type == FormField::TYPE_CHECKBOX) {
                            $selections = json_decode($field->listvalues);
                            if ($field_data->data === null) {
                                $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                    'value' => "",
                                    'created' => $field_data->created_at,
                                    'updated' => $field_data->updated_at
                                ];
                            } else {
                                $data = json_decode($field_data->data);
                                $i = 0;
                                foreach($data as $item) {
                                    foreach($selections as $selection) {
                                        if ($selection->id == $item) {
                                            if ($i == 0 || $dataTable[$teacher_am][$field->title][$field_data->record] == "") {
                                                $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                                    'value' => $selection->value,
                                                    'created' => $field_data->created_at,
                                                    'updated' => $field_data->updated_at
                                                ];
                                            }
                                            else {
                                                $dataTable[$teacher_am][$field->title][$field_data->record]['value'] .= ", ".$selection->value;
                                            }
                                        }
                                    }
                                    $i++;
                                }
                            }

                        } elseif ($field->type == FormField::TYPE_NUMBER) {
                            $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                'value' => intval($field_data->data),
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                        else {
                            $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                'value' => $field_data->data,
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                    }
                }
            }
        } else {
            $dataTableColumns = array('Σχολική μονάδα', 'Κωδ. σχολικής μονάδας');
            foreach ($form->form_fields as $field) {
                array_push($dataTableColumns, $field->title);
                foreach ($field->field_data as $field_data) {
                    if ($field_data->school->active == 1) {
                        if ($field->type == FormField::TYPE_RADIO_BUTTON || $field->type == FormField::TYPE_SELECT) {
                            $selections = json_decode($field->listvalues);
                            foreach($selections as $selection) {
                                if ($selection->id == $field_data->data) {
                                    $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                        'value' => $selection->value,
                                        'created' => $field_data->created_at,
                                        'updated' => $field_data->updated_at
                                    ];
                                }
                            }
                        } elseif ($field->type == FormField::TYPE_CHECKBOX) {
                            $selections = json_decode($field->listvalues);
                            if ($field_data->data === null) {
                                $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                    'value' => "",
                                    'created' => $field_data->created_at,
                                    'updated' => $field_data->updated_at
                                ];
                            } else {
                                $data = json_decode($field_data->data);
                                $i = 0;
                                foreach($data as $item) {
                                    foreach($selections as $selection) {
                                        if ($selection->id == $item) {
                                            if ($i == 0 || $dataTable[$field_data->school->code][$field->title][$field_data->record] == "") {
                                                $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                                    'value' => $selection->value,
                                                    'created' => $field_data->created_at,
                                                    'updated' => $field_data->updated_at
                                                ];
                                            }
                                            else {
                                                $dataTable[$field_data->school->code][$field->title][$field_data->record]['value'] .= ", ".$selection->value;
                                            }
                                        }
                                    }
                                    $i++;
                                }
                            }

                        } elseif ($field->type == FormField::TYPE_NUMBER) {
                            $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                'value' => intval($field_data->data),
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                        else {
                            $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                'value' => $field_data->data,
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                    }
                }
            }
        }

        array_push($dataTableColumns, 'Δημιουργήθηκε', 'Ενημερώθηκε');

        if ($form->for_teachers) {
            // Βρες όλους τους εκπαιδευτικούς που απάντησαν
            $teacher_ids = array_unique($teacher_ids);
            $other_teacher_ids = array_unique($other_teacher_ids);
            $teachers = Teacher::where('active', 1)
                ->whereIn('id', $teacher_ids)
                ->get();
            if ($form->for_all_teachers) {
                $other_teachers = OtherTeacher::whereIn('id', $other_teacher_ids)->get();
            }
        } else {
            // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
            $schools = $form->schools->where('active', 1);
            $categories = $form->school_categories;
            foreach($categories as $category) {
                $schools = $schools->concat($category->schools->where('active', 1));
            }
            $schools = $schools->unique('id');
        }

        $fname = "/tmp/".Str::limit(Str::slug($form->title, '_'), 15)."-".now()->timestamp.".csv";
        $fd = fopen($fname, 'w');
        if ($fd === false) {
            die('Failed to open temporary file');
        }

        fputcsv($fd, $dataTableColumns);
        $row = array();
        if ($form->for_teachers) {
            if (isset($teachers)) {
                foreach($teachers as $teacher) {
                    // Βρες τον μέγιστο αριθμό των εγγραφών για τον εκπαιδευτικό
                    $records = count($dataTable[$teacher->am][$dataTableColumns[3]] ?? ['']);

                    for ($i = 0; $i < $records; $i++) {
                        array_push($row, $teacher->surname.' '.$teacher->name, $teacher->am);
                        $created_at = "";
                        $updated_at = "";
                        foreach(array_slice($dataTableColumns, 2, -2) as $column) {
                            array_push($row, $dataTable[$teacher->am][$column][$i]['value'] ?? '');
                            if (isset($dataTable[$teacher->am][$column][$i]['created'])) {
                                $temp_created_at = new \DateTime($dataTable[$teacher->am][$column][$i]['created']);
                                if ($created_at == "") {
                                    $created_at = $temp_created_at;
                                }
                                else {
                                    if ($created_at > $temp_created_at) {
                                        $created_at = $temp_created_at;
                                    }
                                }
                            }
                            if (isset($dataTable[$teacher->am][$column][$i]['updated'])) {
                                $temp_updated_at = new \DateTime($dataTable[$teacher->am][$column][$i]['updated']);
                                if ($updated_at == "") {
                                    $updated_at = $temp_updated_at;
                                }
                                else {
                                    if ($updated_at < $temp_updated_at) {
                                        $updated_at = $temp_updated_at;
                                    }
                                }
                            }
                        }
                        $created_string = $created_at == "" ? '' : $created_at->format('Y-m-d H:i');
                        $updated_string = $updated_at == "" ? '' : $updated_at->format('Y-m-d H:i');
                        array_push($row, $created_string, $updated_string);
                        fputcsv($fd, $row);
                        $row = array();
                    }
                }
            }
            if (isset($other_teachers)) {
                foreach($other_teachers as $teacher) {
                    // Βρες τον μέγιστο αριθμό των εγγραφών για τον εκπαιδευτικό
                    $records = count($dataTable[$teacher->employeenumber][$dataTableColumns[3]] ?? ['']);

                    for ($i = 0; $i < $records; $i++) {
                        array_push($row, $teacher->name, $teacher->employeenumber);
                        $created_at = "";
                        $updated_at = "";
                        foreach(array_slice($dataTableColumns, 2, -2) as $column) {
                            array_push($row, $dataTable[$teacher->employeenumber][$column][$i]['value'] ?? '');
                            if (isset($dataTable[$teacher->employeenumber][$column][$i]['created'])) {
                                $temp_created_at = new \DateTime($dataTable[$teacher->employeenumber][$column][$i]['created']);
                                if ($created_at == "") {
                                    $created_at = $temp_created_at;
                                }
                                else {
                                    if ($created_at > $temp_created_at) {
                                        $created_at = $temp_created_at;
                                    }
                                }
                            }
                            if (isset($dataTable[$teacher->employeenumber][$column][$i]['updated'])) {
                                $temp_updated_at = new \DateTime($dataTable[$teacher->employeenumber][$column][$i]['updated']);
                                if ($updated_at == "") {
                                    $updated_at = $temp_updated_at;
                                }
                                else {
                                    if ($updated_at < $temp_updated_at) {
                                        $updated_at = $temp_updated_at;
                                    }
                                }
                            }
                        }
                        $created_string = $created_at == "" ? '' : $created_at->format('Y-m-d H:i');
                        $updated_string = $updated_at == "" ? '' : $updated_at->format('Y-m-d H:i');
                        array_push($row, $created_string, $updated_string);
                        fputcsv($fd, $row);
                        $row = array();
                    }
                }
            }
        } else {
            foreach($schools as $school) {
                // Βρες τον μέγιστο αριθμό των εγγραφών για το σχολείο
                $records = count($dataTable[$school->code][$dataTableColumns[3]] ?? ['']);

                $school = School::where('code', $school->code)->first();
                for ($i = 0; $i < $records; $i++) {
                    array_push($row, $school->name, $school->code);
                    $created_at = "";
                    $updated_at = "";
                    foreach(array_slice($dataTableColumns, 2, -2) as $column) {
                        array_push($row, $dataTable[$school->code][$column][$i]['value'] ?? '');
                        if (isset($dataTable[$school->code][$column][$i]['created'])) {
                            $temp_created_at = new \DateTime($dataTable[$school->code][$column][$i]['created']);
                            if ($created_at == "") {
                                $created_at = $temp_created_at;
                            }
                            else {
                                if ($created_at > $temp_created_at) {
                                    $created_at = $temp_created_at;
                                }
                            }
                        }
                        if (isset($dataTable[$school->code][$column][$i]['updated'])) {
                            $temp_updated_at = new \DateTime($dataTable[$school->code][$column][$i]['updated']);
                            if ($updated_at == "") {
                                $updated_at = $temp_updated_at;
                            }
                            else {
                                if ($updated_at < $temp_updated_at) {
                                    $updated_at = $temp_updated_at;
                                }
                            }
                        }
                    }
                    $created_string = $created_at == "" ? '' : $created_at->format('Y-m-d H:i');
                    $updated_string = $updated_at == "" ? '' : $updated_at->format('Y-m-d H:i');
                    array_push($row, $created_string, $updated_string);
                    fputcsv($fd, $row);
                    $row = array();
                }
            }
        }

        fclose($fd);

        return response()->download($fname);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     *
     * @param  \App\Models\Form  $form
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function formDataXLSX(Form $form) : \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $form->load(
                'form_fields',
                'form_fields.field_data',
                'form_fields.field_data.school',
                'form_fields.field_data.teacher',
                'form_fields.field_data.other_teacher'
        );

        $teacher_ids = []; // Πίνακας για να κρατήσουμε τα id των εκπαιδευτικών που απάντησαν
        $other_teacher_ids = [];
        if ($form->for_teachers) {
            $dataTableColumns = array('Εκπαιδευτικός', 'ΑΜ/ΑΦΜ');
            foreach ($form->form_fields as $field) {
                array_push($dataTableColumns, $field->title);
                foreach ($field->field_data as $field_data) {
                    if ($field_data->teacher?->active == 1 || $field_data->other_teacher) {
                        if ($field_data->teacher) {
                            array_push($teacher_ids, $field_data->teacher->id);
                            $teacher_am = $field_data->teacher->am;
                        } else {
                            array_push($other_teacher_ids, $field_data->other_teacher->id);
                            $teacher_am = $field_data->other_teacher->employeenumber;
                        }
                        if ($field->type == FormField::TYPE_RADIO_BUTTON || $field->type == FormField::TYPE_SELECT) {
                            $selections = json_decode($field->listvalues);
                            foreach($selections as $selection) {
                                if ($selection->id == $field_data->data) {
                                    $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                        'value' => $selection->value,
                                        'created' => $field_data->created_at,
                                        'updated' => $field_data->updated_at
                                    ];
                                }
                            }
                        } elseif ($field->type == FormField::TYPE_CHECKBOX) {
                            $selections = json_decode($field->listvalues);
                            if ($field_data->data === null) {
                                $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                    'value' => "",
                                    'created' => $field_data->created_at,
                                    'updated' => $field_data->updated_at
                                ];
                            } else {
                                $data = json_decode($field_data->data);
                                $i = 0;
                                foreach($data as $item) {
                                    foreach($selections as $selection) {
                                        if ($selection->id == $item) {
                                            if ($i == 0 || $dataTable[$teacher_am][$field->title][$field_data->record] == "") {
                                                $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                                    'value' => $selection->value,
                                                    'created' => $field_data->created_at,
                                                    'updated' => $field_data->updated_at
                                                ];
                                            }
                                            else {
                                                $dataTable[$teacher_am][$field->title][$field_data->record]['value'] .= ", ".$selection->value;
                                            }
                                        }
                                    }
                                    $i++;
                                }
                            }

                        } elseif ($field->type == FormField::TYPE_NUMBER) {
                            $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                'value' => intval($field_data->data),
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                        else {
                            $dataTable[$teacher_am][$field->title][$field_data->record] = [
                                'value' => $field_data->data,
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                    }
                }
            }
        } else {
            $dataTableColumns = array('Σχολική μονάδα', 'Κωδ. σχολικής μονάδας');
            foreach ($form->form_fields as $field) {
                array_push($dataTableColumns, $field->title);
                foreach ($field->field_data as $field_data) {
                    if ($field_data->school->active == 1) {
                        if ($field->type == FormField::TYPE_RADIO_BUTTON || $field->type == FormField::TYPE_SELECT) {
                            $selections = json_decode($field->listvalues);
                            foreach($selections as $selection) {
                                if ($selection->id == $field_data->data) {
                                    $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                        'value' => $selection->value,
                                        'created' => $field_data->created_at,
                                        'updated' => $field_data->updated_at
                                    ];
                                }
                            }
                        } elseif ($field->type == FormField::TYPE_CHECKBOX) {
                            $selections = json_decode($field->listvalues);
                            if ($field_data->data === null) {
                                $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                    'value' => "",
                                    'created' => $field_data->created_at,
                                    'updated' => $field_data->updated_at
                                ];
                            } else {
                                $data = json_decode($field_data->data);
                                $i = 0;
                                foreach($data as $item) {
                                    foreach($selections as $selection) {
                                        if ($selection->id == $item) {
                                            if ($i == 0 || $dataTable[$field_data->school->code][$field->title][$field_data->record] == "") {
                                                $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                                    'value' => $selection->value,
                                                    'created' => $field_data->created_at,
                                                    'updated' => $field_data->updated_at
                                                ];
                                            }
                                            else {
                                                $dataTable[$field_data->school->code][$field->title][$field_data->record]['value'] .= ", ".$selection->value;
                                            }
                                        }
                                    }
                                    $i++;
                                }
                            }

                        } elseif ($field->type == FormField::TYPE_NUMBER) {
                            $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                'value' => intval($field_data->data),
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                        else {
                            $dataTable[$field_data->school->code][$field->title][$field_data->record] = [
                                'value' => $field_data->data,
                                'created' => $field_data->created_at,
                                'updated' => $field_data->updated_at
                            ];
                        }
                    }
                }
            }
        }

        array_push($dataTableColumns, 'Δημιουργήθηκε', 'Ενημερώθηκε');

        if ($form->for_teachers) {
            // Βρες όλους τους εκπαιδευτικούς που απάντησαν
            $teacher_ids = array_unique($teacher_ids);
            $other_teacher_ids = array_unique($other_teacher_ids);
            $teachers = Teacher::where('active', 1)
                ->whereIn('id', $teacher_ids)
                ->get();
            if ($form->for_all_teachers) {
                $other_teachers = OtherTeacher::whereIn('id', $other_teacher_ids)->get();
            }
        } else {
            // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
            $schools = $form->schools->where('active', 1);
            $categories = $form->school_categories;
            foreach($categories as $category) {
                $schools = $schools->concat($category->schools->where('active', 1));
            }
            $schools = $schools->unique('id');
        }

        $fname = "/tmp/".Str::limit(Str::slug($form->title, '_'), 15)."-".now()->timestamp.".xlsx";
        $writer = new XLSXWriter();

        $data = array();
        array_push($data, $dataTableColumns);
        $row = array();

        if ($form->for_teachers) {
            if (isset($teachers)) {
                foreach($teachers as $teacher) {
                    // Βρες τον μέγιστο αριθμό των εγγραφών για το σχολείο
                    $records = count($dataTable[$teacher->am][$dataTableColumns[3]] ?? ['']);

                    for ($i = 0; $i < $records; $i++) {
                        array_push($row, $teacher->surname.' '.$teacher->name, $teacher->am);
                        $created_at = "";
                        $updated_at = "";
                        foreach(array_slice($dataTableColumns, 2, -2) as $column) {
                            array_push($row, $dataTable[$teacher->am][$column][$i]['value'] ?? '');
                            if (isset($dataTable[$teacher->am][$column][$i]['created'])) {
                                $temp_created_at = new \DateTime($dataTable[$teacher->am][$column][$i]['created']);
                                if ($created_at == "") {
                                    $created_at = $temp_created_at;
                                }
                                else {
                                    if ($created_at > $temp_created_at) {
                                        $created_at = $temp_created_at;
                                    }
                                }
                            }
                            if (isset($dataTable[$teacher->am][$column][$i]['updated'])) {
                                $temp_updated_at = new \DateTime($dataTable[$teacher->am][$column][$i]['updated']);
                                if ($updated_at == "") {
                                    $updated_at = $temp_updated_at;
                                }
                                else {
                                    if ($updated_at < $temp_updated_at) {
                                        $updated_at = $temp_updated_at;
                                    }
                                }
                            }
                        }
                        $created_string = $created_at == "" ? '' : $created_at->format('Y-m-d H:i');
                        $updated_string = $updated_at == "" ? '' : $updated_at->format('Y-m-d H:i');
                        array_push($row, $created_string, $updated_string);
                        array_push($data, $row);
                        $row = array();
                    }
                }
            }
            if (isset($other_teachers)) {
                foreach($other_teachers as $teacher) {
                    // Βρες τον μέγιστο αριθμό των εγγραφών για το σχολείο
                    $records = count($dataTable[$teacher->employeenumber][$dataTableColumns[3]] ?? ['']);

                    for ($i = 0; $i < $records; $i++) {
                        array_push($row, $teacher->name, $teacher->employeenumber);
                        $created_at = "";
                        $updated_at = "";
                        foreach(array_slice($dataTableColumns, 2, -2) as $column) {
                            array_push($row, $dataTable[$teacher->employeenumber][$column][$i]['value'] ?? '');
                            if (isset($dataTable[$teacher->employeenumber][$column][$i]['created'])) {
                                $temp_created_at = new \DateTime($dataTable[$teacher->employeenumber][$column][$i]['created']);
                                if ($created_at == "") {
                                    $created_at = $temp_created_at;
                                }
                                else {
                                    if ($created_at > $temp_created_at) {
                                        $created_at = $temp_created_at;
                                    }
                                }
                            }
                            if (isset($dataTable[$teacher->employeenumber][$column][$i]['updated'])) {
                                $temp_updated_at = new \DateTime($dataTable[$teacher->employeenumber][$column][$i]['updated']);
                                if ($updated_at == "") {
                                    $updated_at = $temp_updated_at;
                                }
                                else {
                                    if ($updated_at < $temp_updated_at) {
                                        $updated_at = $temp_updated_at;
                                    }
                                }
                            }
                        }
                        $created_string = $created_at == "" ? '' : $created_at->format('Y-m-d H:i');
                        $updated_string = $updated_at == "" ? '' : $updated_at->format('Y-m-d H:i');
                        array_push($row, $created_string, $updated_string);
                        array_push($data, $row);
                        $row = array();
                    }
                }
            }
        } else {
            foreach($schools as $school) {
                // Βρες τον μέγιστο αριθμό των εγγραφών για το σχολείο
                $records = count($dataTable[$school->code][$dataTableColumns[3]] ?? ['']);

                $school = School::where('code', $school->code)->first();
                for ($i = 0; $i < $records; $i++) {
                    array_push($row, $school->name, $school->code);
                    $created_at = "";
                    $updated_at = "";
                    foreach(array_slice($dataTableColumns, 2, -2) as $column) {
                        array_push($row, $dataTable[$school->code][$column][$i]['value'] ?? '');
                        if (isset($dataTable[$school->code][$column][$i]['created'])) {
                            $temp_created_at = new \DateTime($dataTable[$school->code][$column][$i]['created']);
                            if ($created_at == "") {
                                $created_at = $temp_created_at;
                            }
                            else {
                                if ($created_at > $temp_created_at) {
                                    $created_at = $temp_created_at;
                                }
                            }
                        }
                        if (isset($dataTable[$school->code][$column][$i]['updated'])) {
                            $temp_updated_at = new \DateTime($dataTable[$school->code][$column][$i]['updated']);
                            if ($updated_at == "") {
                                $updated_at = $temp_updated_at;
                            }
                            else {
                                if ($updated_at < $temp_updated_at) {
                                    $updated_at = $temp_updated_at;
                                }
                            }
                        }
                    }
                    $created_string = $created_at == "" ? '' : $created_at->format('Y-m-d H:i');
                    $updated_string = $updated_at == "" ? '' : $updated_at->format('Y-m-d H:i');
                    array_push($row, $created_string, $updated_string);
                    array_push($data, $row);
                    $row = array();
                }
            }
        }

        $writer->writeSheet($data);
        $writer->writeToFile($fname);

        return response()->download($fname);
    }

    /**
     * Αλλαγή κατάστασης φόρμας.
     *
     * @param  \App\Models\Form  $form
     * @param  int  $state
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setActive(Form $form, int $state) : \Illuminate\Http\RedirectResponse
    {
        if (in_array($state, [0, 1])) {
            $form->active = $state;
            $form->save();

            return redirect(route('admin.form.index'))->with('status', 'Η φόρμα '.($form->active ? 'ενεργοποιήθηκε' : 'απενεργοποιήθηκε'));
        }
    }

    /**
     * Εναλλαγή κατάστασης φόρμας (από ενεργή σε ανενεργή και το ανάποδο).
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(Form $form) : \Illuminate\Http\RedirectResponse
    {
        $form->active = $form->active ? 0 : 1;
        $form->save();

        return redirect(route('admin.form.index'))->with('status', 'Η φόρμα '.($form->active ? 'ενεργοποιήθηκε' : 'απενεργοποιήθηκε'));
    }

    /**
     * Εμφάνιση σχολικών μονάδων/εκπαιδευτικών που δεν απάντησαν.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Contracts\View\View
     */
    public function missing(Form $form) : \Illuminate\Contracts\View\View
    {
        $filtered_schools = null;
        $filtered_teachers = null;
        if (!$form->for_teachers) {
            $schools = $form->schools()->where('active', 1)->get();
            foreach($form->school_categories()->get() as $category) {
                $schools = $schools->merge($category->schools()->where('active', 1)->get());
            }
            $schools = $schools->unique('id');
            $data = $form->data()->get();
            $answer = [];
            $data->each(function($item, $key) use (&$answer) {
                $answer[$item->school_id] = true;
            });
            $seen = [];
            $filtered_schools = $schools->filter(function($school, $key) use ($answer, &$seen) {
                if (in_array($school, $seen) || isset($answer[$school->id])) {
                    return false;
                }

                array_push($seen, $school);
                return true;
            });
        } else {
            $teachers = Teacher::where('active', 1)->get();
            $data = $form->data()->get();
            $answer = [];
            $data->each(function($item, $key) use (&$answer) {
                $answer[$item->teacher_id] = true;
            });
            $seen = [];
            $filtered_teachers = $teachers->filter(function($teacher, $key) use ($answer, &$seen) {
                if (in_array($teacher, $seen) || isset($answer[$teacher->id])) {
                    return false;
                }

                array_push($seen, $teacher);
                return true;
            });

        }
        return view('admin.form.missing')
            ->with('form', $form)
            ->with('schools', $filtered_schools)
            ->with('teachers', $filtered_teachers);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     *
     * @param  \App\Models\Form  $form
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function missingCSV(Form $form) : \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $fname = "/tmp/".Str::limit(Str::slug($form->title, '_'), 15)."-".now()->timestamp."-missing.csv";
        $fd = fopen($fname, 'w');
        if ($fd === false) {
            die('Failed to open temporary file');
        }

        if ($form->for_teachers) {
            $dataTableColumns = array('Εκπαιδευτικός', 'ΑΜ/ΑΦΜ', 'Τηλέφωνο');

            // Βρες όλους τους εκπαιδευτικούς που θα έπρεπε να απαντήσουν
            $teachers = Teacher::where('active', 1)->get();
            $data = $form->data()->get();
            $answer = [];
            $data->each(function($item, $key) use (&$answer) {
                $answer[$item->teacher_id] = true;
            });
            $seen = [];
            $filtered_teachers = $teachers->filter(function($teacher, $key) use ($answer, &$seen) {
                if (in_array($teacher, $seen) || isset($answer[$teacher->id])) {
                    return false;
                }

                array_push($seen, $teacher);
                return true;
            });

            fputcsv($fd, $dataTableColumns);
            foreach($filtered_teachers as $teacher) {
                fputcsv($fd, [$teacher->name, $teacher->code, '']);
            }
        } else {
            $dataTableColumns = array('Σχολική μονάδα', 'Κωδ. σχολικής μονάδας', 'Τηλέφωνο');

            // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
            $schools = $form->schools()->where('active', 1)->get();
            foreach($form->school_categories()->get() as $category) {
                $schools = $schools->merge($category->schools()->where('active', 1)->get());
            }
            $schools = $schools->unique('id');
            $data = $form->data()->get();
            $answer = [];
            $data->each(function($item, $key) use (&$answer) {
                $answer[$item->school_id] = true;
            });
            $seen = [];
            $filtered_schools = $schools->filter(function($school, $key) use ($answer, &$seen) {
                if (in_array($school, $seen) || isset($answer[$school->id])) {
                    return false;
                }

                array_push($seen, $school);
                return true;
            });

            fputcsv($fd, $dataTableColumns);
            foreach($filtered_schools as $school) {
                fputcsv($fd, [$school->name, $school->code, $school->telephone]);
            }
        }

        fclose($fd);

        return response()->download($fname);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     *
     * @param  \App\Models\Form  $form
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function missingXLSX(Form $form) : \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $fname = "/tmp/".Str::limit(Str::slug($form->title, '_'), 15)."-".now()->timestamp."-missing.xlsx";
        $writer = new XLSXWriter();

        if ($form->for_teachers) {
            $dataTableColumns = array('Εκπαιδευτικός', 'ΑΜ/ΑΦΜ', 'Τηλέφωνο');

            // Βρες όλους τους εκπαιδευτικούς που θα έπρεπε να απαντήσουν
            $teachers = Teacher::where('active', 1)->get();
            $data = $form->data()->get();
            $answer = [];
            $data->each(function($item, $key) use (&$answer) {
                $answer[$item->teacher_id] = true;
            });
            $seen = [];
            $filtered_teachers = $teachers->filter(function($teacher, $key) use ($answer, &$seen) {
                if (in_array($teacher, $seen) || isset($answer[$teacher->id])) {
                    return false;
                }

                array_push($seen, $teacher);
                return true;
            });

            $data = array();
            array_push($data, $dataTableColumns);
            foreach($filtered_teachers as $teacher) {
                array_push($data, [$teacher->name, $teacher->code, '']);
            }
        } else {
            $dataTableColumns = array('Σχολική μονάδα', 'Κωδ. σχολικής μονάδας', 'Τηλέφωνο');

            // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
            $schools = $form->schools()->where('active', 1)->get();
            foreach($form->school_categories()->get() as $category) {
                $schools = $schools->merge($category->schools()->where('active', 1)->get());
            }
            $schools = $schools->unique('id');
            $data = $form->data()->get();
            $answer = [];
            $data->each(function($item, $key) use (&$answer) {
                $answer[$item->school_id] = true;
            });
            $seen = [];
            $filtered_schools = $schools->filter(function($school, $key) use ($answer, &$seen) {
                if (in_array($school, $seen) || isset($answer[$school->id])) {
                    return false;
                }

                array_push($seen, $school);
                return true;
            });

            $data = array();
            array_push($data, $dataTableColumns);
            foreach($filtered_schools as $school) {
                array_push($data, [$school->name, $school->code, $school->telephone]);
            }
        }

        $writer->writeSheet($data);
        $writer->writeToFile($fname);

        return response()->download($fname);
    }

    /**
     * Αντιγραφή φόρμας
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function copyForm(Form $form) : \Illuminate\Http\RedirectResponse
    {
        // Δημιουργία αντιγράφου
        $form_clone = $form->replicate();
        $form_clone->user_id = Auth::user()->id;
        $form_clone->save();

        foreach($form->form_fields()->get() as $item) {
            $field = new FormField;
            $field->sort_id = $item->sort_id;
            $field->title = $item->title;
            $field->type = $item->type;
            $field->listvalues = $item->listvalues;
            $form_clone->form_fields()->save($field);
        }

        foreach ($form->school_categories()->get() as $category) {
            $form_clone->school_categories()->attach($category);
        }

        foreach ($form->schools()->get() as $school) {
            $form_clone->schools()->attach($school);
        }

        return redirect(route('admin.form.index'))->with('status', 'Το αντίγραφο της φόρμας δημιουργήθηκε');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Contracts\View\View
     */
    public function confirmDelete(Form $form) : \Illuminate\Contracts\View\View
    {
        return view('admin.form.confirm_delete')->with('form', $form);
    }
}
