<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcceptedFiletype;
use App\Models\Form;
use App\Models\FormField;
use App\Models\OtherTeacher;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\SelectionList;
use App\Models\Teacher;
use App\Services\FormDataTableService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use XLSXWriter;
use ZipArchive;

class FormsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private FormDataTableService $formDataTableService)
    {
        $this->authorizeResource(Form::class, 'form');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Contracts\View\View
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

        $forms = Form::fetchWithPagination($filter, $only_active);

        return view('admin.form.index')
            ->with('forms', $forms)
            ->with('filter', $filter)
            ->with('only_active', $only_active);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Contracts\View\View
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
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        DB::beginTransaction();

        $form = Form::fromRequest($request);

        FormField::fromRequest($request, $form);

        // Έλεγχος αν οι κατηγορίες υπάρχουν και δημιουργία πίνακα
        $categories = SchoolCategory::whereIn(
            'id',
            explode(',', $request->get('categories'))
        )->get();

        foreach ($categories as $category) {
            $form->school_categories()->attach($category);
        }

        // Έλεγχος αν τα σχολεία υπάρχουν και δημιουργία πίνακα
        $schools = School::whereIn(
            'id',
            explode(',', $request->get('schools'))
        )->get();

        foreach ($schools as $school) {
            $form->schools()->attach($school);
        }

        DB::commit();

        return redirect(route('admin.form.index'))->with('status', 'Η φόρμα δημιουργήθηκε');
    }

    /**
     * Display the specified resource.
     */
    public function show(Form $form): \Illuminate\Contracts\View\View
    {
        return view('admin.form.show')->with('form', $form);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Form $form): \Illuminate\Contracts\View\View
    {
        $schools = School::get(['id', 'name', 'active']);
        $categories = SchoolCategory::all('id', 'name');
        $accepted_filetypes = AcceptedFiletype::all();

        $school_selected_values = [];
        foreach ($form->schools as $school) {
            array_push($school_selected_values, $school->id);
        }

        $category_selected_values = [];
        foreach ($form->school_categories as $category) {
            array_push($category_selected_values, $category->id);
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
    public function update(Request $request, Form $form): \Illuminate\Http\RedirectResponse
    {
        $this->validate($request, [
            'title' => 'required',
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
            explode(',', $request->get('categories'))
        )->get();

        $form->school_categories()->sync($categories);

        // Έλεγχος αν τα σχολεία υπάρχουν και δημιουργία πίνακα
        $schools = School::whereIn(
            'id',
            explode(',', $request->get('schools'))
        )->get();

        $form->schools()->sync($schools);

        DB::commit();

        return redirect(route('admin.form.index'))->with('status', 'Η φόρμα ενημερώθηκε');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Form $form): \Illuminate\Http\RedirectResponse
    {
        $form->form_fields()->delete();
        $form->delete();

        return redirect(route('admin.form.index'))->with('status', 'Η φόρμα διαγράφηκε');
    }

    /**
     * Παρουσίαση δεδομένων φόρμας.
     */
    public function formData(Form $form): \Illuminate\Contracts\View\View
    {
        $form->load(
            'form_fields',
            'form_fields.field_data',
            'form_fields.field_data.school',
            'form_fields.field_data.teacher',
            'form_fields.field_data.other_teacher'
        );

        [$dataTableColumns, $dataTable, $links] = $this
            ->formDataTableService
            ->useLinks()
            ->usePagination()
            ->create($form);
        // dd($dataTableColumns, $dataTable, $links);

        $schools = [];
        if (! $form->for_teachers) {
            // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
            $schools = $form->schools->where('active', 1);
            $categories = $form->school_categories;
            foreach ($categories as $category) {
                $schools = $schools->concat($category->schools->where('active', 1));
            }
            $schools = $schools->unique('id')->toArray();
        }

        return view('admin.form.data')
            ->with('dataTable', $dataTable)
            ->with('dataTableColumns', $dataTableColumns)
            ->with('schools', array_values($schools))
            ->with('form', $form)
            ->with('links', $links);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     */
    public function formDataCSV(Form $form): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $form->load(
            'form_fields',
            'form_fields.field_data',
            'form_fields.field_data.school',
            'form_fields.field_data.teacher',
            'form_fields.field_data.other_teacher'
        );

        [$dataTableColumns, $dataTable, $teacher_ids, $other_teacher_ids] = $this->formDataTableService->create($form);

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
            foreach ($categories as $category) {
                $schools = $schools->concat($category->schools->where('active', 1));
            }
            $schools = $schools->unique('id');
        }

        $fname = '/tmp/'.Str::limit(Str::slug($form->title, '_'), 15).'-'.now()->timestamp.'.csv';
        $fd = fopen($fname, 'w');
        if ($fd === false) {
            exit('Failed to open temporary file');
        }

        fputcsv($fd, $dataTableColumns);
        $row = [];
        if ($form->for_teachers) {
            if (isset($teachers)) {
                foreach ($teachers as $teacher) {
                    // Βρες τον μέγιστο αριθμό των εγγραφών για τον εκπαιδευτικό
                    $records = count($dataTable[$teacher->am][$dataTableColumns[3]] ?? ['']);

                    for ($i = 0; $i < $records; $i++) {
                        array_push($row, $teacher->surname.' '.$teacher->name, $teacher->am);
                        $created_at = '';
                        $updated_at = '';
                        foreach (array_slice($dataTableColumns, 2, -2) as $column) {
                            array_push($row, $dataTable[$teacher->am][$column][$i]['value'] ?? '');
                            if (isset($dataTable[$teacher->am][$column][$i]['created'])) {
                                $temp_created_at = new \DateTime($dataTable[$teacher->am][$column][$i]['created']);
                                if ($created_at == '') {
                                    $created_at = $temp_created_at;
                                } else {
                                    if ($created_at > $temp_created_at) {
                                        $created_at = $temp_created_at;
                                    }
                                }
                            }
                            if (isset($dataTable[$teacher->am][$column][$i]['updated'])) {
                                $temp_updated_at = new \DateTime($dataTable[$teacher->am][$column][$i]['updated']);
                                if ($updated_at == '') {
                                    $updated_at = $temp_updated_at;
                                } else {
                                    if ($updated_at < $temp_updated_at) {
                                        $updated_at = $temp_updated_at;
                                    }
                                }
                            }
                        }
                        $created_string = $created_at == '' ? '' : $created_at->format('Y-m-d H:i');
                        $updated_string = $updated_at == '' ? '' : $updated_at->format('Y-m-d H:i');
                        array_push($row, $created_string, $updated_string);
                        fputcsv($fd, $row);
                        $row = [];
                    }
                }
            }
            if (isset($other_teachers)) {
                foreach ($other_teachers as $teacher) {
                    // Βρες τον μέγιστο αριθμό των εγγραφών για τον εκπαιδευτικό
                    $records = count($dataTable[$teacher->employeenumber][$dataTableColumns[3]] ?? ['']);

                    for ($i = 0; $i < $records; $i++) {
                        array_push($row, $teacher->name, $teacher->employeenumber);
                        $created_at = '';
                        $updated_at = '';
                        foreach (array_slice($dataTableColumns, 2, -2) as $column) {
                            array_push($row, $dataTable[$teacher->employeenumber][$column][$i]['value'] ?? '');
                            if (isset($dataTable[$teacher->employeenumber][$column][$i]['created'])) {
                                $temp_created_at = new \DateTime($dataTable[$teacher->employeenumber][$column][$i]['created']);
                                if ($created_at == '') {
                                    $created_at = $temp_created_at;
                                } else {
                                    if ($created_at > $temp_created_at) {
                                        $created_at = $temp_created_at;
                                    }
                                }
                            }
                            if (isset($dataTable[$teacher->employeenumber][$column][$i]['updated'])) {
                                $temp_updated_at = new \DateTime($dataTable[$teacher->employeenumber][$column][$i]['updated']);
                                if ($updated_at == '') {
                                    $updated_at = $temp_updated_at;
                                } else {
                                    if ($updated_at < $temp_updated_at) {
                                        $updated_at = $temp_updated_at;
                                    }
                                }
                            }
                        }
                        $created_string = $created_at == '' ? '' : $created_at->format('Y-m-d H:i');
                        $updated_string = $updated_at == '' ? '' : $updated_at->format('Y-m-d H:i');
                        array_push($row, $created_string, $updated_string);
                        fputcsv($fd, $row);
                        $row = [];
                    }
                }
            }
        } else {
            foreach ($schools as $school) {
                // Βρες τον μέγιστο αριθμό των εγγραφών για το σχολείο
                $records = count($dataTable[$school->code][$dataTableColumns[3]] ?? ['']);

                $school = School::where('code', $school->code)->first();
                for ($i = 0; $i < $records; $i++) {
                    array_push($row, $school->name, $school->code);
                    $created_at = '';
                    $updated_at = '';
                    foreach (array_slice($dataTableColumns, 2, -2) as $column) {
                        array_push($row, $dataTable[$school->code][$column][$i]['value'] ?? '');
                        if (isset($dataTable[$school->code][$column][$i]['created'])) {
                            $temp_created_at = new \DateTime($dataTable[$school->code][$column][$i]['created']);
                            if ($created_at == '') {
                                $created_at = $temp_created_at;
                            } else {
                                if ($created_at > $temp_created_at) {
                                    $created_at = $temp_created_at;
                                }
                            }
                        }
                        if (isset($dataTable[$school->code][$column][$i]['updated'])) {
                            $temp_updated_at = new \DateTime($dataTable[$school->code][$column][$i]['updated']);
                            if ($updated_at == '') {
                                $updated_at = $temp_updated_at;
                            } else {
                                if ($updated_at < $temp_updated_at) {
                                    $updated_at = $temp_updated_at;
                                }
                            }
                        }
                    }
                    $created_string = $created_at == '' ? '' : $created_at->format('Y-m-d H:i');
                    $updated_string = $updated_at == '' ? '' : $updated_at->format('Y-m-d H:i');
                    array_push($row, $created_string, $updated_string);
                    fputcsv($fd, $row);
                    $row = [];
                }
            }
        }

        fclose($fd);

        return response()->download($fname);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     */
    public function formDataXLSX(Form $form): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $form->load(
            'form_fields',
            'form_fields.field_data',
            'form_fields.field_data.school',
            'form_fields.field_data.teacher',
            'form_fields.field_data.other_teacher'
        );

        [$dataTableColumns, $dataTable, $teacher_ids, $other_teacher_ids] = $this->formDataTableService->create($form);

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
            foreach ($categories as $category) {
                $schools = $schools->concat($category->schools->where('active', 1));
            }
            $schools = $schools->unique('id');
        }

        $fname = '/tmp/'.Str::limit(Str::slug($form->title, '_'), 15).'-'.now()->timestamp.'.xlsx';
        $writer = new XLSXWriter();

        $data = [];
        array_push($data, $dataTableColumns);
        $row = [];

        if ($form->for_teachers) {
            if (isset($teachers)) {
                foreach ($teachers as $teacher) {
                    // Βρες τον μέγιστο αριθμό των εγγραφών για το σχολείο
                    $records = count($dataTable[$teacher->am][$dataTableColumns[3]] ?? ['']);

                    for ($i = 0; $i < $records; $i++) {
                        array_push($row, $teacher->surname.' '.$teacher->name, $teacher->am);
                        $created_at = '';
                        $updated_at = '';
                        foreach (array_slice($dataTableColumns, 2, -2) as $column) {
                            array_push($row, $dataTable[$teacher->am][$column][$i]['value'] ?? '');
                            if (isset($dataTable[$teacher->am][$column][$i]['created'])) {
                                $temp_created_at = new \DateTime($dataTable[$teacher->am][$column][$i]['created']);
                                if ($created_at == '') {
                                    $created_at = $temp_created_at;
                                } else {
                                    if ($created_at > $temp_created_at) {
                                        $created_at = $temp_created_at;
                                    }
                                }
                            }
                            if (isset($dataTable[$teacher->am][$column][$i]['updated'])) {
                                $temp_updated_at = new \DateTime($dataTable[$teacher->am][$column][$i]['updated']);
                                if ($updated_at == '') {
                                    $updated_at = $temp_updated_at;
                                } else {
                                    if ($updated_at < $temp_updated_at) {
                                        $updated_at = $temp_updated_at;
                                    }
                                }
                            }
                        }
                        $created_string = $created_at == '' ? '' : $created_at->format('Y-m-d H:i');
                        $updated_string = $updated_at == '' ? '' : $updated_at->format('Y-m-d H:i');
                        array_push($row, $created_string, $updated_string);
                        array_push($data, $row);
                        $row = [];
                    }
                }
            }
            if (isset($other_teachers)) {
                foreach ($other_teachers as $teacher) {
                    // Βρες τον μέγιστο αριθμό των εγγραφών για το σχολείο
                    $records = count($dataTable[$teacher->employeenumber][$dataTableColumns[3]] ?? ['']);

                    for ($i = 0; $i < $records; $i++) {
                        array_push($row, $teacher->name, $teacher->employeenumber);
                        $created_at = '';
                        $updated_at = '';
                        foreach (array_slice($dataTableColumns, 2, -2) as $column) {
                            array_push($row, $dataTable[$teacher->employeenumber][$column][$i]['value'] ?? '');
                            if (isset($dataTable[$teacher->employeenumber][$column][$i]['created'])) {
                                $temp_created_at = new \DateTime($dataTable[$teacher->employeenumber][$column][$i]['created']);
                                if ($created_at == '') {
                                    $created_at = $temp_created_at;
                                } else {
                                    if ($created_at > $temp_created_at) {
                                        $created_at = $temp_created_at;
                                    }
                                }
                            }
                            if (isset($dataTable[$teacher->employeenumber][$column][$i]['updated'])) {
                                $temp_updated_at = new \DateTime($dataTable[$teacher->employeenumber][$column][$i]['updated']);
                                if ($updated_at == '') {
                                    $updated_at = $temp_updated_at;
                                } else {
                                    if ($updated_at < $temp_updated_at) {
                                        $updated_at = $temp_updated_at;
                                    }
                                }
                            }
                        }
                        $created_string = $created_at == '' ? '' : $created_at->format('Y-m-d H:i');
                        $updated_string = $updated_at == '' ? '' : $updated_at->format('Y-m-d H:i');
                        array_push($row, $created_string, $updated_string);
                        array_push($data, $row);
                        $row = [];
                    }
                }
            }
        } else {
            foreach ($schools as $school) {
                // Βρες τον μέγιστο αριθμό των εγγραφών για το σχολείο
                $records = count($dataTable[$school->code][$dataTableColumns[3]] ?? ['']);

                $school = School::where('code', $school->code)->first();
                for ($i = 0; $i < $records; $i++) {
                    array_push($row, $school->name, $school->code);
                    $created_at = '';
                    $updated_at = '';
                    foreach (array_slice($dataTableColumns, 2, -2) as $column) {
                        array_push($row, $dataTable[$school->code][$column][$i]['value'] ?? '');
                        if (isset($dataTable[$school->code][$column][$i]['created'])) {
                            $temp_created_at = new \DateTime($dataTable[$school->code][$column][$i]['created']);
                            if ($created_at == '') {
                                $created_at = $temp_created_at;
                            } else {
                                if ($created_at > $temp_created_at) {
                                    $created_at = $temp_created_at;
                                }
                            }
                        }
                        if (isset($dataTable[$school->code][$column][$i]['updated'])) {
                            $temp_updated_at = new \DateTime($dataTable[$school->code][$column][$i]['updated']);
                            if ($updated_at == '') {
                                $updated_at = $temp_updated_at;
                            } else {
                                if ($updated_at < $temp_updated_at) {
                                    $updated_at = $temp_updated_at;
                                }
                            }
                        }
                    }
                    $created_string = $created_at == '' ? '' : $created_at->format('Y-m-d H:i');
                    $updated_string = $updated_at == '' ? '' : $updated_at->format('Y-m-d H:i');
                    array_push($row, $created_string, $updated_string);
                    array_push($data, $row);
                    $row = [];
                }
            }
        }

        $writer->writeSheet($data);
        $writer->writeToFile($fname);

        return response()->download($fname);
    }

    /**
     * Αλλαγή κατάστασης φόρμας.
     */
    public function setActive(Form $form, int $state): \Illuminate\Http\RedirectResponse
    {
        if (in_array($state, [0, 1])) {
            $form->active = $state;
            $form->save();

            return redirect(route('admin.form.index'))->with('status', 'Η φόρμα '.($form->active ? 'ενεργοποιήθηκε' : 'απενεργοποιήθηκε'));
        }
    }

    /**
     * Εναλλαγή κατάστασης φόρμας (από ενεργή σε ανενεργή και το ανάποδο).
     */
    public function toggleActive(Form $form): \Illuminate\Http\RedirectResponse
    {
        $form->active = $form->active ? 0 : 1;
        $form->save();

        return redirect(route('admin.form.index'))->with('status', 'Η φόρμα '.($form->active ? 'ενεργοποιήθηκε' : 'απενεργοποιήθηκε'));
    }

    /**
     * Εμφάνιση σχολικών μονάδων/εκπαιδευτικών που δεν απάντησαν.
     */
    public function missing(Form $form): \Illuminate\Contracts\View\View
    {
        $filtered_schools = null;
        $filtered_teachers = null;
        if (! $form->for_teachers) {
            $schools = $form->schools()->where('active', 1)->get();
            foreach ($form->school_categories()->get() as $category) {
                $schools = $schools->merge($category->schools()->where('active', 1)->get());
            }
            $schools = $schools->unique('id');
            $data = $form->data()->get();
            $answer = [];
            $data->each(function ($item, $key) use (&$answer) {
                $answer[$item->school_id] = true;
            });
            $seen = [];
            $filtered_schools = $schools->filter(function ($school, $key) use ($answer, &$seen) {
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
            $data->each(function ($item, $key) use (&$answer) {
                $answer[$item->teacher_id] = true;
            });
            $seen = [];
            $filtered_teachers = $teachers->filter(function ($teacher, $key) use ($answer, &$seen) {
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
     */
    public function missingCSV(Form $form): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $fname = '/tmp/'.Str::limit(Str::slug($form->title, '_'), 15).'-'.now()->timestamp.'-missing.csv';
        $fd = fopen($fname, 'w');
        if ($fd === false) {
            exit('Failed to open temporary file');
        }

        if ($form->for_teachers) {
            $dataTableColumns = ['Εκπαιδευτικός', 'ΑΜ/ΑΦΜ', 'Τηλέφωνο'];

            // Βρες όλους τους εκπαιδευτικούς που θα έπρεπε να απαντήσουν
            $teachers = Teacher::where('active', 1)->get();
            $data = $form->data()->get();
            $answer = [];
            $data->each(function ($item, $key) use (&$answer) {
                $answer[$item->teacher_id] = true;
            });
            $seen = [];
            $filtered_teachers = $teachers->filter(function ($teacher, $key) use ($answer, &$seen) {
                if (in_array($teacher, $seen) || isset($answer[$teacher->id])) {
                    return false;
                }

                array_push($seen, $teacher);

                return true;
            });

            fputcsv($fd, $dataTableColumns);
            foreach ($filtered_teachers as $teacher) {
                fputcsv($fd, [$teacher->name, $teacher->code, '']);
            }
        } else {
            $dataTableColumns = ['Σχολική μονάδα', 'Κωδ. σχολικής μονάδας', 'Τηλέφωνο'];

            // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
            $schools = $form->schools()->where('active', 1)->get();
            foreach ($form->school_categories()->get() as $category) {
                $schools = $schools->merge($category->schools()->where('active', 1)->get());
            }
            $schools = $schools->unique('id');
            $data = $form->data()->get();
            $answer = [];
            $data->each(function ($item, $key) use (&$answer) {
                $answer[$item->school_id] = true;
            });
            $seen = [];
            $filtered_schools = $schools->filter(function ($school, $key) use ($answer, &$seen) {
                if (in_array($school, $seen) || isset($answer[$school->id])) {
                    return false;
                }

                array_push($seen, $school);

                return true;
            });

            fputcsv($fd, $dataTableColumns);
            foreach ($filtered_schools as $school) {
                fputcsv($fd, [$school->name, $school->code, $school->telephone]);
            }
        }

        fclose($fd);

        return response()->download($fname);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     */
    public function missingXLSX(Form $form): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $fname = '/tmp/'.Str::limit(Str::slug($form->title, '_'), 15).'-'.now()->timestamp.'-missing.xlsx';
        $writer = new XLSXWriter();

        if ($form->for_teachers) {
            $dataTableColumns = ['Εκπαιδευτικός', 'ΑΜ/ΑΦΜ', 'Τηλέφωνο'];

            // Βρες όλους τους εκπαιδευτικούς που θα έπρεπε να απαντήσουν
            $teachers = Teacher::where('active', 1)->get();
            $data = $form->data()->get();
            $answer = [];
            $data->each(function ($item, $key) use (&$answer) {
                $answer[$item->teacher_id] = true;
            });
            $seen = [];
            $filtered_teachers = $teachers->filter(function ($teacher, $key) use ($answer, &$seen) {
                if (in_array($teacher, $seen) || isset($answer[$teacher->id])) {
                    return false;
                }

                array_push($seen, $teacher);

                return true;
            });

            $data = [];
            array_push($data, $dataTableColumns);
            foreach ($filtered_teachers as $teacher) {
                array_push($data, [$teacher->name, $teacher->code, '']);
            }
        } else {
            $dataTableColumns = ['Σχολική μονάδα', 'Κωδ. σχολικής μονάδας', 'Τηλέφωνο'];

            // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
            $schools = $form->schools()->where('active', 1)->get();
            foreach ($form->school_categories()->get() as $category) {
                $schools = $schools->merge($category->schools()->where('active', 1)->get());
            }
            $schools = $schools->unique('id');
            $data = $form->data()->get();
            $answer = [];
            $data->each(function ($item, $key) use (&$answer) {
                $answer[$item->school_id] = true;
            });
            $seen = [];
            $filtered_schools = $schools->filter(function ($school, $key) use ($answer, &$seen) {
                if (in_array($school, $seen) || isset($answer[$school->id])) {
                    return false;
                }

                array_push($seen, $school);

                return true;
            });

            $data = [];
            array_push($data, $dataTableColumns);
            foreach ($filtered_schools as $school) {
                array_push($data, [$school->name, $school->code, $school->telephone]);
            }
        }

        $writer->writeSheet($data);
        $writer->writeToFile($fname);

        return response()->download($fname);
    }

    /**
     * Αντιγραφή φόρμας
     */
    public function copyForm(Form $form): \Illuminate\Http\RedirectResponse
    {
        // Δημιουργία αντιγράφου
        $form_clone = $form->replicate();
        $form_clone->user_id = Auth::user()->id;
        $form_clone->save();

        foreach ($form->form_fields()->get() as $item) {
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
     */
    public function confirmDelete(Form $form): \Illuminate\Contracts\View\View
    {
        return view('admin.form.confirm_delete')->with('form', $form);
    }

    public function downloadFile(Form $form, $category, $categoryId, $record, $fieldId)
    {
        // Κάνε έναν απλό έλεγχο για ασφάλεια
        if (! in_array($category, ['school', 'teacher', 'other_teacher']) ||
            ! is_numeric($categoryId) ||
            ! is_numeric($record) ||
            ! is_numeric($fieldId)) {
            abort(404);
        }

        if ($category === 'school') {
            $school = School::find($categoryId);
            if (! $school) {
                abort(404);
            }

            $record_data = $form->data()
                ->where('school_id', $school->id)
                ->where('record', $record)
                ->where('form_field_id', $fieldId)
                ->first();
        }

        if ($category === 'teacher') {
            $teacher = Teacher::find($categoryId);
            if (! $teacher) {
                abort(404);
            }

            $record_data = $form->data()
                ->where('teacher_id', $teacher->id)
                ->where('record', $record)
                ->where('form_field_id', $fieldId)
                ->first();
        }

        if ($category === 'other_teacher') {
            $other_teacher = OtherTeacher::find($categoryId);
            if (! $other_teacher) {
                abort(404);
            }

            $record_data = $form->data()
                ->where('other_teacher_id', $other_teacher->id)
                ->where('record', $record)
                ->where('form_field_id', $fieldId)
                ->first();
        }

        $filename = $record_data->data;
        if (Storage::exists("report/$form->id/$category/$categoryId/$record/$fieldId")) {
            return Storage::download("report/$form->id/$category/$categoryId/$record/$fieldId", $filename);
        } else {
            return redirect(route('admin.form.index'))->with('error', 'Το αρχείο δεν βρέθηκε');
        }
    }

    public function downloadAllFiles(Form $form)
    {
        $fields = $form->form_fields->where('type', FormField::TYPE_FILE);

        if (! $fields) {
            abort(404);
        }

        $zip_path = '/tmp/'.auth()->user()->id.'/';
        Storage::makeDirectory($zip_path);

        // Κάνε εκκαθάριση παλιών αρχείων
        foreach (Storage::files($zip_path) as $file) {
            Storage::delete($file);
        }

        $zip = new ZipArchive();
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $zip_name = $now->format('YmdHisu').'.zip';
        $zip->open(storage_path('app').$zip_path.$zip_name, ZipArchive::CREATE);

        foreach ($fields as $field) {
            $subfolder = mb_strimwidth($field->title, 0, 15, '...');
            foreach ($field->field_data as $data) {
                if ($data->school) {
                    $subfolder2 = "$subfolder/{$data->school->name}/{$data->record}";
                    $local_file = storage_path('app')."/report/{$form->id}/school/{$data->school->id}/{$data->record}/{$field->id}";
                    $zip->addFile($local_file, "$subfolder2/{$data->data}");
                } elseif ($data->teacher) {
                    $subfolder2 = "$subfolder/{$data->teacher->name}/{$data->record}";
                    $local_file = storage_path('app')."/report/{$form->id}/teacher/{$data->teacher->id}/{$data->record}/{$field->id}";
                    $zip->addFile($local_file, "$subfolder2/{$data->data}");
                } else {
                    $subfolder2 = "$subfolder/{$data->other_teacher->name}/{$data->record}";
                    $local_file = storage_path('app')."/report/{$form->id}/other_teacher/{$data->other_teacher->id}/{$data->record}/{$field->id}";
                    $zip->addFile($local_file, "$subfolder2/{$data->data}");
                }
            }
        }

        $zip->close();

        return Storage::download($zip_path.$zip_name);
    }
}
