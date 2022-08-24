<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\FormField;
use App\Models\FormFieldData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $filter = $request->get('filter');
        $only_active = $request->get('only_active') ?? 0;
        if ($filter && $only_active) {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount(['data' => function($query) {
                    $query->where('record', 0);
                }])
                ->where('active', '1')
                ->query(function($query) use ($filter) {
                    $query->where('id', 'like', '%'.$filter.'%')
                    ->orWhere('title', 'like', '%'.$filter.'%');
                })
                ->with('user')
                ->with('schools')
                ->with('form_fields')
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
                ->with('schools')
                ->with('form_fields')
                ->paginate(15);
        }
        else if ($only_active) {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount(['data' => function($query) {
                    $query->where('record', 0);
                }])
                ->where('active', '1')
                ->with('user')
                ->with('schools')
                ->with('form_fields')
                ->paginate(15);
        }
        else {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount(['data' => function($query) {
                    $query->where('record', 0);
                }])
                ->with('user')
                ->with('schools')
                ->with('form_fields')
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
        return view('admin.form.create')
            ->with('schools', $schools)
            ->with('categories', $categories);
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

        // Create form
        $form =  new Form;
        $form->title = $request->input('title');
        $form->notes = $request->input('notes');
        $form->user_id = Auth::id();
        $form->active = true;
        $form->multiple = $request->input('multiple_input') ? true : false;
        $form->save();

        $formfield = $request->input('field');
        foreach(array_keys($formfield) as $key) {
            $field = new FormField;
            $field->sort_id = $formfield[$key]['sort_id'];
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

        // Update form
        $form->title = $request->input('title');
        $form->notes = $request->input('notes');
        $form->user_id = Auth::id();
        $form->multiple = $request->input('multiple_input') ? true : false;
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
                'form_fields.field_data.school'
        );

        $dataTable = array();
        $dataTableColumns = array();
        foreach ($form->form_fields as $field) {
            array_push($dataTableColumns, $field->title);
            foreach ($field->field_data as $field_data) {
                $field_data->school;
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

        // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
        $schools = $form->schools;
        $categories = $form->school_categories;
        foreach($categories as $category) {
            $schools = $schools->concat($category->schools);
        }
        $schools = $schools->unique('id');
        return view('admin.form.data')
            ->with('dataTable', $dataTable)
            ->with('dataTableColumns', $dataTableColumns)
            ->with('schools', array_values($schools->toArray()))
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
                'form_fields.field_data.school'
        );

        $dataTableColumns = array('Σχολική μονάδα', 'Κωδ. σχολικής μονάδας');
        foreach ($form->form_fields as $field) {
            array_push($dataTableColumns, $field->title);
            foreach ($field->field_data as $field_data) {
                $field_data->school;
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

        array_push($dataTableColumns, 'Δημιουργήθηκε', 'Ενημερώθηκε');

        // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
        $schools = $form->schools;
        $categories = $form->school_categories;
        foreach($categories as $category) {
            $schools = $schools->concat($category->schools);
        }
        $schools = $schools->unique('id');

        $fname = "/tmp/".Str::limit(Str::slug($form->title, '_'), 15)."-".now()->timestamp.".csv";
        $fd = fopen($fname, 'w');
        if ($fd === false) {
            die('Failed to open temporary file');
        }

        fputcsv($fd, $dataTableColumns);
        $row = array();
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
                'form_fields.field_data.school'
        );

        $dataTableColumns = array('Σχολική μονάδα', 'Κωδ. σχολικής μονάδας');
        foreach ($form->form_fields as $field) {
            array_push($dataTableColumns, $field->title);
            foreach ($field->field_data as $field_data) {
                $field_data->school;
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

        array_push($dataTableColumns, 'Δημιουργήθηκε', 'Ενημερώθηκε');

        // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
        $schools = $form->schools;
        $categories = $form->school_categories;
        foreach($categories as $category) {
            $schools = $schools->concat($category->schools);
        }
        $schools = $schools->unique('id');

        $fname = "/tmp/".Str::limit(Str::slug($form->title, '_'), 15)."-".now()->timestamp.".xlsx";
        $writer = new XLSXWriter();

        $data = array();
        array_push($data, $dataTableColumns);
        $row = array();
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
     * Εμφάνιση σχολικών μονάδων που δεν απάντησαν.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Contracts\View\View
     */
    public function missing(Form $form) : \Illuminate\Contracts\View\View
    {
        $schools = $form->schools()->get();
        foreach($form->school_categories()->get() as $category) {
            $schools = $schools->merge($category->schools()->get());
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
        return view('admin.form.missing')
            ->with('form', $form)
            ->with('schools', $filtered_schools);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     *
     * @param  \App\Models\Form  $form
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function missingCSV(Form $form) : \Symfony\Component\HttpFoundation\BinaryFileResponse
    {

        $dataTableColumns = array('Σχολική μονάδα', 'Κωδ. σχολικής μονάδας', 'Τηλέφωνο');

        // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
        $schools = $form->schools()->get();
        foreach($form->school_categories()->get() as $category) {
            $schools = $schools->merge($category->schools()->get());
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

        $fname = "/tmp/".Str::limit(Str::slug($form->title, '_'), 15)."-".now()->timestamp."-missing.csv";
        $fd = fopen($fname, 'w');
        if ($fd === false) {
            die('Failed to open temporary file');
        }

        fputcsv($fd, $dataTableColumns);
        foreach($filtered_schools as $school) {
            fputcsv($fd, [$school->name, $school->code, $school->telephone]);
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
        $dataTableColumns = array('Σχολική μονάδα', 'Κωδ. σχολικής μονάδας', 'Τηλέφωνο');

        // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
        $schools = $form->schools()->get();
        foreach($form->school_categories()->get() as $category) {
            $schools = $schools->merge($category->schools()->get());
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

        $fname = "/tmp/".Str::limit(Str::slug($form->title, '_'), 15)."-".now()->timestamp."-missing.xlsx";
        $writer = new XLSXWriter();

        $data = array();
        array_push($data, $dataTableColumns);
        foreach($filtered_schools as $school) {
            array_push($data, [$school->name, $school->code, $school->telephone]);
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
