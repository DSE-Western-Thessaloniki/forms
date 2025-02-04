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
use App\Services\FormMissingDataService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public function __construct()
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
    public function formData(Form $form, Request $request, FormDataTableService $formDataTableService): \Illuminate\Contracts\View\View
    {
        $noPagination = $request->get('noPagination');
        $form->load('form_fields');

        if ($noPagination == 1) {
            [$dataTableColumns, $dataTable, $links] = $formDataTableService
                ->useLinks()
                ->create($form);
        } else {
            [$dataTableColumns, $dataTable, $links] = $formDataTableService
                ->useLinks()
                ->usePagination(50)
                ->create($form);
        }

        return view('admin.form.data')
            ->with('dataTable', $dataTable)
            ->with('dataTableColumns', $dataTableColumns)
            ->with('form', $form)
            ->with('links', $links);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     */
    public function formDataCSV(Form $form, FormDataTableService $formDataTableService): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $form->load('form_fields');

        [$dataTableColumns, $dataTable] = $formDataTableService->create($form);

        $fname = '/tmp/'.Str::limit(Str::slug($form->title, '_'), 15).'-'.now()->timestamp.'.csv';
        $fd = fopen($fname, 'w');
        if ($fd === false) {
            exit('Failed to open temporary file');
        }

        fputcsv($fd, $dataTableColumns);

        foreach ($dataTable->toArray() as $row) {
            fputcsv($fd, $row);
        }

        fclose($fd);

        return response()->download($fname);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     */
    public function formDataXLSX(Form $form, FormDataTableService $formDataTableService): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $form->load('form_fields');

        [$dataTableColumns, $dataTable] = $formDataTableService->create($form);

        $fname = '/tmp/'.Str::limit(Str::slug($form->title, '_'), 15).'-'.now()->timestamp.'.xlsx';
        $writer = new XLSXWriter;

        $data = array_merge([$dataTableColumns], $dataTable->toArray());

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
    public function missing(Form $form, FormMissingDataService $formMissingDataService): \Illuminate\Contracts\View\View
    {
        $data = $formMissingDataService->getMissingTable($form);

        return view('admin.form.missing')
            ->with('form', $form)
            ->with('missing_data', $data);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     */
    public function missingCSV(Form $form, FormMissingDataService $formMissingDataService): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $fname = '/tmp/'.Str::limit(Str::slug($form->title, '_'), 15).'-'.now()->timestamp.'-missing.csv';
        $fd = fopen($fname, 'w');
        if ($fd === false) {
            exit('Failed to open temporary file');
        }

        $data = $formMissingDataService->getMissingTable($form);
        foreach ($data as $row) {
            fputcsv($fd, $row);
        }

        fclose($fd);

        return response()->download($fname);
    }

    /**
     * Λήψη δεδομένων φόρμας.
     */
    public function missingXLSX(Form $form, FormMissingDataService $formMissingDataService): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $fname = '/tmp/'.Str::limit(Str::slug($form->title, '_'), 15).'-'.now()->timestamp.'-missing.xlsx';
        $writer = new XLSXWriter;

        $data = $formMissingDataService->getMissingTable($form);
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

        $form_fields = $form->form_fields()->get();
        $new_form_fields = collect();
        foreach ($form_fields as $item) {
            $field = new FormField;
            $field->sort_id = $item->sort_id;
            $field->title = $item->title;
            $field->type = $item->type;
            $field->required = $item->required;
            $field->listvalues = $item->listvalues;
            $field->options = $item->options;
            $result = $form_clone->form_fields()->save($field);

            // Αν δεν απέτυχε η αποθήκευση
            if ($result) {
                $new_form_fields->push($field);
            }
        }

        // Διόρθωσε τα options όπου χρειάζεται για την εμφάνιση πεδίων
        $new_form_fields->each(function ($field) use ($form_fields, $new_form_fields) {
            $options = json_decode($field->options, true);
            Log::info(print_r($options, true));
            if (! isset($options['show_when'])) {
                return;
            }

            for ($i = 0; $i < count($options['show_when']); $i++) {
                if (! isset($options['show_when'][$i]['active_field'])) {
                    continue;
                }
                // Το id του πεδίου είναι λάθος γιατί περιέχει το πεδίο της αρχικής φόρμας.
                // Κάνε σύνδεση με το νέο πεδίο
                $old_field_id = $options['show_when'][$i]['active_field'];
                $found = false;
                $idx = 0;
                while (! $found && $idx < count($form_fields)) {
                    Log::info('Comparing: '.$form_fields[$idx]->id.' with '.$old_field_id);
                    if ($form_fields[$idx]->id == $old_field_id) {
                        $found = true;

                        Log::info('Found!');

                        continue;
                    }
                    $idx++;
                }

                // Log::info('Found: '.$found ? 'true' : 'false');
                if ($found) {
                    $options['show_when'][$i]['active_field'] = $new_form_fields[$idx]->id;
                    Log::info(print_r($options, true));
                    $field->options = json_encode($options);
                    $field->save();
                }
            }

        });

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

        // Αν περαστεί λάθος record
        if (! $record_data) {
            abort(404);
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

        $zip = new ZipArchive;
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $zip_name = $now->format('YmdHisu').'.zip';
        $zip->open(storage_path('app').$zip_path.$zip_name, ZipArchive::CREATE);

        foreach ($fields as $field) {
            $subfolder = mb_strimwidth($field->title, 0, 15, '...');
            foreach ($field->field_data as $data) {
                if ($data->school) {
                    if ($form->multiple) {
                        $subfolder2 = "$subfolder/{$data->school->name}/{$data->record}";
                    } else {
                        $subfolder2 = "$subfolder/{$data->school->name}";
                    }
                    $local_file = storage_path('app')."/report/{$form->id}/school/{$data->school->id}/{$data->record}/{$field->id}";
                    $zip->addFile($local_file, "$subfolder2/{$data->data}");
                } elseif ($data->teacher) {
                    if ($form->multiple) {
                        $subfolder2 = "$subfolder/{$data->teacher->surname} {$data->teacher->name} {$data->teacher->am}/{$data->record}";
                    } else {
                        $subfolder2 = "$subfolder/{$data->teacher->surname} {$data->teacher->name} {$data->teacher->am}";
                    }
                    $local_file = storage_path('app')."/report/{$form->id}/teacher/{$data->teacher->id}/{$data->record}/{$field->id}";
                    $zip->addFile($local_file, "$subfolder2/{$data->data}");
                } else {
                    if ($form->multiple) {
                        $subfolder2 = "$subfolder/{$data->other_teacher->name}/{$data->record}";
                    } else {
                        $subfolder2 = "$subfolder/{$data->other_teacher->name}";
                    }
                    $local_file = storage_path('app')."/report/{$form->id}/other_teacher/{$data->other_teacher->id}/{$data->record}/{$field->id}";
                    $zip->addFile($local_file, "$subfolder2/{$data->data}");
                }
            }
        }

        $zip->close();

        return Storage::download($zip_path.$zip_name);
    }
}
