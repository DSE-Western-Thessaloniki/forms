<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReportRequest;
use App\Http\Traits\UsesCasAccessFiltering;
use App\Http\Traits\UsesFileFiltering;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportsController extends Controller
{
    use UsesCasAccessFiltering, UsesFileFiltering;

    /**
     * Create a new controller instance.
     */
    public function __construct() {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $login_category = cas()->getAttribute('businesscategory');
        if ($login_category === 'ΕΚΠΑΙΔΕΥΤΙΚΟΣ' || $login_category === 'ΠΡΟΣΩΠΙΚΟ') { // Εκπαιδευτικός
            $teacher = session()->get('teacher');
            if ($teacher instanceof Teacher) {
                $forms = Form::where('active', true)
                    ->where('for_teachers', 1)
                    ->with('user')
                    ->orderBy('created_at', 'desc')->paginate(15);

                return view('report.index')->with('forms', $forms);
            }

            $forms = Form::where('active', true)
                ->where('for_all_teachers', 1)
                ->with('user')
                ->orderBy('created_at', 'desc')->paginate(15);

            return view('report.index')->with('forms', $forms);
        }

        // Τότε μάλλον σχολείο
        $school = session()->get('school');
        if ($school) {
            $categories = $school->categories->pluck('id');
            $forms = Form::where('active', true)
                ->where(function ($query) use ($school, $categories): void {
                    // Προσθήκη παρένθεσης
                    $query->whereHas('schools', function ($q) use ($school): void {
                        $q->where('school_id', $school->id);
                    })
                        ->when($categories, function ($q) use ($categories): void {
                            // Αν το σχολείο ανήκει σε μια τουλάχιστον κατηγορία
                            $q->orWhereHas('school_categories', function ($q) use ($categories): void {
                                $q->whereIn('school_category_id', $categories);
                            });
                        });
                })
                ->with('user')
                ->orderBy('created_at', 'desc')->paginate(15);

            return view('report.index')->with('forms', $forms);
        }

        Log::warning('Το uid:'.cas()->getAttribute('uid').' και το email:'.cas()->getAttribute('mail').' δεν αντιστοιχούν σε λογαριασμό.');

        return view('pages.deny_access');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {
        $form = Form::where('active', true)->find($id);
        if ($form) {
            $access = $this->school_or_teacher_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $school = session()->get('school');
                    $teacher = session()->get('teacher');
                    $other_teacher = session()->get('other_teacher');
                    if ($school !== null) { // Σχολείο
                        $record_data = $form->data()->where('school_id', $school->id)->where('record', 0)->get();
                    } elseif ($teacher !== null) { // Εκπαιδευτικός
                        $record_data = $form->data()->where('teacher_id', $teacher->id)->where('record', 0)->get();
                    } else {
                        $record_data = $form->data()->where('other_teacher_id', $other_teacher->id)->where('record', 0)->get();
                    }

                    $data_dict = [];
                    foreach ($record_data as $item) {
                        $data_dict[$item->form_field_id] = $item->data;
                    }

                    return view('report.show')
                        ->with('form', $form)
                        ->with('data_dict', $data_dict)
                        ->with('school', $school)
                        ->with('teacher', $teacher)
                        ->with('other_teacher', $other_teacher);
                }

                return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        return to_route('report.index')->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }

    /**
     * Display the specified resource.
     */
    public function showRecord(string $id, int $record): View|RedirectResponse
    {
        $form = Form::where('active', true)->find($id);
        if ($form) {
            $access = $this->school_or_teacher_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $school = session()->get('school');
                    $teacher = session()->get('teacher');
                    $other_teacher = session()->get('other_teacher');
                    if ($school !== null) { // Σχολείο
                        $record_data = $form->data()->where('school_id', $school->id)->where('record', $record)->get();
                    } elseif ($teacher !== null) { // Εκπαιδευτικός
                        $record_data = $form->data()->where('teacher_id', $teacher->id)->where('record', $record)->get();
                    } else {
                        $record_data = $form->data()->where('other_teacher_id', $other_teacher->id)->where('record', $record)->get();
                    }

                    $data_dict = [];
                    foreach ($record_data as $item) {
                        $data_dict[$item->form_field_id] = $item->data;
                    }

                    return view('report.show')
                        ->with('form', $form)
                        ->with('record', $record)
                        ->with('data_dict', $data_dict)
                        ->with('school', $school)
                        ->with('teacher', $teacher)
                        ->with('other_teacher', $other_teacher);
                }

                return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        return to_route('report.index')->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View|RedirectResponse
    {
        $form = Form::with('form_fields')
            ->where('active', true)
            ->find($id);
        if ($form) {
            $access = $this->school_or_teacher_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $school = session()->get('school');
                    $teacher = session()->get('teacher');
                    $other_teacher = session()->get('other_teacher');
                    if ($school !== null) { // Σχολείο
                        $record_data = $form->data()->where('school_id', $school->id)->where('record', 0)->get();
                    } elseif ($teacher !== null) { // Εκπαιδευτικός της Διεύθυνσης
                        $record_data = $form->data()->where('teacher_id', $teacher->id)->where('record', 0)->get();
                    } else {
                        $record_data = $form->data()->where('other_teacher_id', $other_teacher->id)->where('record', 0)->get();
                    }

                    $data_dict = [];
                    foreach ($record_data as $item) {
                        $data_dict[$item->form_field_id] = $item->data;
                    }

                    return view('report.edit')
                        ->with('form', $form)
                        ->with('data_dict', $data_dict)
                        ->with('school', $school)
                        ->with('teacher', $teacher)
                        ->with('other_teacher', $other_teacher);
                }

                return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        $form = Form::where('active', false)->find($id);
        if ($form) {
            return to_route('report.index')->with('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
        }

        return to_route('report.index')->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editRecord(string $id, int $record): View|RedirectResponse
    {
        $form = Form::with('form_fields')
            ->where('active', true)
            ->find($id);
        if ($form) {
            $access = $this->school_or_teacher_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    if ($form->multiple) {
                        $school = session()->get('school');
                        $teacher = session()->get('teacher');
                        $other_teacher = session()->get('other_teacher');
                        if ($school !== null) { // Σχολείο
                            $record_data = $form->data()->where('school_id', $school->id)->where('record', $record)->get();
                        } elseif ($teacher !== null) { // Εκπαιδευτικός
                            $record_data = $form->data()->where('teacher_id', $teacher->id)->where('record', $record)->get();
                        } else {
                            $record_data = $form->data()->where('other_teacher_id', $other_teacher->id)->where('record', $record)->get();
                        }

                        $data_dict = [];
                        foreach ($record_data as $item) {
                            $data_dict[$item->form_field_id] = $item->data;
                        }

                        return view('report.edit')
                            ->with('form', $form)
                            ->with('record', $record)
                            ->with('data_dict', $data_dict)
                            ->with('school', $school)
                            ->with('teacher', $teacher)
                            ->with('other_teacher', $other_teacher);
                    }

                    return to_route('report.index')->with('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');
                }

                return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        $form = Form::where('active', false)->find($id);
        if ($form) {
            return to_route('report.index')->with('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
        }

        return to_route('report.index')->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportRequest $request, string $id): RedirectResponse|View
    {
        $form = Form::with('form_fields')->where('active', true)->find($id);
        if ($form) {
            $access = $this->school_or_teacher_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $school = session()->get('school');
                    $teacher = session()->get('teacher');
                    $other_teacher = session()->get('other_teacher');

                    $fields = $form->form_fields;
                    foreach ($fields as $field) {
                        if ($field->readonly()) {
                            continue;
                        }

                        if ($field->type === FormField::TYPE_FILE) {
                            $file = $request->file('f'.$field->id);
                            if ($school !== null) {
                                $subfolder = 'school';
                                $subfolderId = $school->id;
                                $data_field_key = 'school_id';
                            } elseif ($teacher !== null) {
                                $subfolder = 'teacher';
                                $subfolderId = $teacher->id;
                                $data_field_key = 'teacher_id';
                            } else {
                                $subfolder = 'other_teacher';
                                $subfolderId = $other_teacher->id;
                                $data_field_key = 'other_teacher_id';
                            }

                            if ($file) {
                                $file->storeAs("report/{$form->id}/$subfolder/$subfolderId/0", "{$field->id}");
                                $data = $this->filterFilename($file->getClientOriginalName());
                            } else {
                                // Αν δεν έχουμε νέα δεδομένα αρχείου έλεγξε μήπως έχουμε
                                // ήδη ανεβάσει αρχείο και κράτησε τα στοιχεία του
                                $field_data = $field->field_data
                                    ->where('record', 0)
                                    ->where($data_field_key, $subfolderId)
                                    ->first();
                                $data = $field_data?->data;
                            }
                        } else {
                            $data = $request->input('f'.$field->id);
                        }

                        if (is_array($data)) {
                            $data = json_encode($data);
                        }
                        if ($school !== null) {
                            $field->field_data()
                                ->updateOrCreate(
                                    ['school_id' => $school->id],
                                    ['data' => $data, 'updated_at' => now()]
                                );
                        } elseif ($teacher !== null) {
                            $field->field_data()
                                ->updateOrCreate(
                                    ['teacher_id' => $teacher->id],
                                    ['data' => $data, 'updated_at' => now()]
                                );
                        } else {
                            $field->field_data()
                                ->updateOrCreate(
                                    ['other_teacher_id' => $other_teacher->id],
                                    ['data' => $data, 'updated_at' => now()]
                                );
                        }
                    }

                    return to_route('report.index')->with('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');
                }

                return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        $form = Form::where('active', false)->find($id);
        if ($form) {
            return to_route('report.index')->with('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
        }

        return to_route('report.index')->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }

    /**
     * Update the specified resource in storage and move to the "next" record.
     */
    public function updateRecord(UpdateReportRequest $request, string $id, int $record, int|string $next): RedirectResponse|View
    {
        $form = Form::with('form_fields')->where('active', true)->find($id);
        if ($form) {
            $access = $this->school_or_teacher_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $school = session()->get('school');
                    $teacher = session()->get('teacher');
                    $other_teacher = session()->get('other_teacher');

                    $fields = $form->form_fields;
                    foreach ($fields as $field) {
                        if ($field->readonly()) {
                            continue;
                        }

                        if ($field->type === FormField::TYPE_FILE) {
                            $file = $request->file('f'.$field->id);
                            if ($school !== null) {
                                $subfolder = 'school';
                                $subfolderId = $school->id;
                                $data_field_key = 'school_id';
                            } elseif ($teacher !== null) {
                                $subfolder = 'teacher';
                                $subfolderId = $teacher->id;
                                $data_field_key = 'teacher_id';
                            } else {
                                $subfolder = 'other_teacher';
                                $subfolderId = $other_teacher->id;
                                $data_field_key = 'other_teacher_id';
                            }

                            if ($file) {
                                $file->storeAs("report/{$form->id}/$subfolder/$subfolderId/$record", "{$field->id}");
                                $data = $this->filterFilename($file->getClientOriginalName());
                            } else {
                                // Αν δεν έχουμε νέα δεδομένα αρχείου έλεγξε μήπως έχουμε
                                // ήδη ανεβάσει αρχείο και κράτησε τα στοιχεία του
                                $field_data = $field->field_data
                                    ->where('record', $record)
                                    ->where($data_field_key, $subfolderId)
                                    ->first();
                                $data = $field_data?->data;
                            }
                        } else {
                            $data = $request->input('f'.$field->id);
                        }

                        if (is_array($data)) {
                            $data = json_encode($data);
                        }
                        if ($school !== null) { // Σχολείο
                            $field->field_data()
                                ->updateOrCreate(
                                    ['school_id' => $school->id, 'record' => $record],
                                    ['data' => $data, 'updated_at' => now()]
                                );
                        } elseif ($teacher !== null) { // Εκπαιδευτικός
                            $field->field_data()
                                ->updateOrCreate(
                                    ['teacher_id' => $teacher->id, 'record' => $record],
                                    ['data' => $data, 'updated_at' => now()]
                                );
                        } else {
                            $field->field_data()
                                ->updateOrCreate(
                                    ['other_teacher_id' => $other_teacher->id, 'record' => $record],
                                    ['data' => $data, 'updated_at' => now()]
                                );
                        }
                    }

                    // Που πάμε τώρα;
                    if ($next === 'new') {
                        // Βρες την τελευταία εγγραφή
                        $last_record = 0;

                        if ($school !== null) { // Σχολείο
                            foreach ($fields as $field) {
                                if ($last_record < $field->field_data->where('school_id', $school->id)->count()) {
                                    $last_record = $field->field_data->where('school_id', $school->id)->count();
                                }
                            }
                            // Ετοίμασε τις εγγραφές στον πίνακα
                            foreach ($fields as $field) {
                                $data = null;
                                $field->field_data()->updateOrCreate(
                                    ['school_id' => $school->id, 'record' => $last_record],
                                    ['data' => $data, 'updated_at' => now()]
                                );
                            }
                        } elseif ($teacher !== null) { // Εκπαιδευτικός
                            foreach ($fields as $field) {
                                if ($last_record < $field->field_data->where('teacher_id', $teacher->id)->count()) {
                                    $last_record = $field->field_data->where('teacher_id', $teacher->id)->count();
                                }
                            }
                            // Ετοίμασε τις εγγραφές στον πίνακα
                            foreach ($fields as $field) {
                                $data = null;
                                $field->field_data()->updateOrCreate(
                                    ['teacher_id' => $teacher->id, 'record' => $last_record],
                                    ['data' => $data, 'updated_at' => now()]
                                );
                            }
                        } else {
                            foreach ($fields as $field) {
                                if ($last_record < $field->field_data->where('other_teacher_id', $other_teacher->id)->count()) {
                                    $last_record = $field->field_data->where('other_teacher_id', $other_teacher->id)->count();
                                }
                            }
                            // Ετοίμασε τις εγγραφές στον πίνακα
                            foreach ($fields as $field) {
                                $data = null;
                                $field->field_data()->updateOrCreate(
                                    ['other_teacher_id' => $other_teacher->id, 'record' => $last_record],
                                    ['data' => $data, 'updated_at' => now()]
                                );
                            }
                        }

                        return to_route('report.edit.record', ['report' => $id, 'record' => $last_record])->with('success', 'Η αναφορά ενημερώθηκε');
                    }

                    if ($next === 'exit') {
                        return to_route('report.index')->with('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');
                    }

                    if ($next === 'next') {
                        return to_route('report.edit.record', ['report' => $id, 'record' => $record + 1])->with('success', 'Η αναφορά ενημερώθηκε');
                    }

                    if ($next === 'prev') {
                        return to_route('report.edit.record', ['report' => $id, 'record' => $record - 1])->with('success', 'Η αναφορά ενημερώθηκε');
                    }

                    if (is_numeric($next)) {
                        return to_route('report.edit.record', ['report' => $id, 'record' => intval($next)])->with('success', 'Η αναφορά ενημερώθηκε');
                    }

                    return to_route('report.index')->with('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');
                }

                return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο ή εκπαιδευτικό
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        $form = Form::where('active', false)->find($id);
        if ($form) {
            return to_route('report.index')->with('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
        }

        return to_route('report.index')->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }

    public function downloadFile(Form $report, string $fieldId, string $record): StreamedResponse|RedirectResponse|View
    {
        // Κάνε έναν απλό έλεγχο για ασφάλεια
        if (! is_numeric($record) ||
            ! is_numeric($fieldId)) {
            abort(404);
        }

        if ($report->active) {
            $access = $this->school_or_teacher_has_access($report);
            if (is_bool($access)) {
                if ($access) {
                    $school = session()->get('school');
                    $teacher = session()->get('teacher');
                    $other_teacher = session()->get('other_teacher');

                    if ($school !== null) {
                        $subfolder = "school/{$school->id}";
                        $record_data = $report->data()
                            ->where('school_id', $school->id)
                            ->where('record', $record)
                            ->where('form_field_id', $fieldId)
                            ->first();
                    } elseif ($teacher !== null) {
                        $subfolder = "teacher/{$teacher->id}";
                        $record_data = $report->data()
                            ->where('teacher_id', $teacher->id)
                            ->where('record', $record)
                            ->where('form_field_id', $fieldId)
                            ->first();
                    } else {
                        $subfolder = "other_teacher/{$other_teacher->id}";
                        $record_data = $report->data()
                            ->where('other_teacher_id', $other_teacher->id)
                            ->where('record', $record)
                            ->where('form_field_id', $fieldId)
                            ->first();
                    }

                    $path = "report/{$report->id}/$subfolder/$record/$fieldId";
                    if (Storage::exists($path)) {

                        return Storage::download($path, $record_data->data);
                    }

                    return to_route('report.index')->with('error', 'Το αρχείο δεν βρέθηκε');
                }

                return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            return $access;
        }

        return to_route('report.index')->with('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
    }

    public function destroyRecord(string $report, int $record): RedirectResponse|View
    {
        $form = Form::where('active', true)->find($report);
        if ($form) {
            $access = $this->school_or_teacher_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $school = session()->get('school');
                    $teacher = session()->get('teacher');
                    $other_teacher = session()->get('other_teacher');

                    if ($school !== null) {
                        $form->data()
                            ->where('school_id', $school->id)
                            ->where('record', $record)
                            ->delete();

                        if ($form->multiple) {
                            $form->data()
                                ->where('school_id', $school->id)
                                ->where('record', '>', $record)
                                ->decrement('record');
                        }
                    } elseif ($teacher !== null) {
                        $form->data()
                            ->where('teacher_id', $teacher->id)
                            ->where('record', $record)
                            ->delete();

                        if ($form->multiple) {
                            $form->data()
                                ->where('teacher_id', $teacher->id)
                                ->where('record', '>', $record)
                                ->decrement('record');
                        }
                    } else {
                        $form->data()
                            ->where('other_teacher_id', $other_teacher->id)
                            ->where('record', $record)
                            ->delete();

                        if ($form->multiple) {
                            $form->data()
                                ->where('other_teacher_id', $other_teacher->id)
                                ->where('record', '>', $record)
                                ->decrement('record');
                        }
                    }

                    if ($form->multiple) {
                        return to_route('report.edit.record', ['report' => $report, 'record' => 0])->with('success', 'Η εγγραφή διαγράφηκε');
                    }

                    return to_route('report.edit', ['report' => $report])->with('success', 'Η εγγραφή διαγράφηκε');
                }

                return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            return $access;
        }

        return to_route('report.index')->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }
}
