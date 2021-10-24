<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Create a new controller instance.
     * @param Form $form
     * @return bool|Illuminate\Support\Facades\View
     */
    private function school_has_access(Form $form)
    {
        $school = School::where('username', cas()->getAttribute('uid'))->first();

        if (!$school) { // Αν ο λογαριασμός δεν αντιστοιχεί σε σχολική μονάδα
            return view('pages.deny_access');
        }

        $categories = $school->categories;
        $form_categories = $form->school_categories;
        $in_category = false;
        foreach ($categories as $category) {
            if ($form_categories->contains($category))
                $in_category = true;
        }

        return ($form->schools()->where('school_id', $school->id)->count() > 0 || $in_category);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = cas()->getAttribute('uid');
        $school = School::where('username', $id)->first();
        if ($school) {
            $categories = $school->categories;
            $forms = Form::whereHas('schools', function ($q) use ($school) {
                    $q->where('school_id', $school->id);
                })
                ->when($categories, function ($q) use ($categories) { // Αν το σχολείο ανήκει σε μια τουλάχιστον κατηγορία
                    $q->orWhereHas('school_categories', function ($q) use ($categories) {
                        $q->whereIn('school_category_id', $categories);
                    });

                })
                ->orderBy('created_at', 'desc')->paginate(15);
            return view('report.index')->with('forms', $forms);
        }

        return view('pages.deny_access');
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
        if ($form) {
            $access = $this->school_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    return view('report.show')->with('form', $form);
                }

                return redirect(route('report.index'))->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        return redirect(route('report.index'))->with('error', 'Λάθος αναγνωριστικό φόρμας');
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
            $access = $this->school_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    return view('report.edit')->with('form', $form);
                }

                return redirect(route('report.index'))->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        return redirect(route('report.index'))->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editRecord($id, $record)
    {
        $form = Form::find($id);
        if ($form) {
            $access = $this->school_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    if ($form->multiple) {
                        return view('report.edit')
                            ->with('form', $form)
                            ->with('record', $record);
                    }

                    return redirect(route('report.index'))->with('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');
                }

                return redirect(route('report.index'))->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        return redirect(route('report.index'))->with('error', 'Λάθος αναγνωριστικό φόρμας');
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
        $form = Form::with('form_fields')->find($id);
        if ($form) {
            $access = $this->school_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $fields = $form->form_fields;
                    foreach ($fields as $field) {
                        $data = $request->input("f".$field->id);
                        if (is_array($data)) {
                            $data = json_encode($data);
                        }
                        $field->field_data()->updateOrCreate(['school_id' => School::where('username', cas()->getAttribute('uid'))->first()->id], ['data' => $data]);
                    }

                    return redirect(route('report.index'))->with('success', 'Η αναφορά ενημερώθηκε');
                }

                return redirect(route('report.index'))->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        return redirect(route('report.index'))->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }

    /**
     * Update the specified resource in storage and move to the "next" record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id The form id
     * @param  int $record The record to be saved
     * @param  int|String $next The next record to go to
     * @return \Illuminate\Http\Response
     */
    public function updateRecord(Request $request, $id, $record, $next)
    {
        $form = Form::with('form_fields')->find($id);
        if ($form) {
            $access = $this->school_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $fields = $form->form_fields;
                    foreach ($fields as $field) {
                        $data = $request->input("f".$field->id);
                        if (is_array($data)) {
                            $data = json_encode($data);
                        }
                        $field->field_data()->updateOrCreate(['school_id' => School::where('username', cas()->getAttribute('uid'))->first()->id, 'record' => $record], ['data' => $data]);
                    }

                    // Που πάμε τώρα;
                    if ($next === 'new') {
                        // Βρες την τελευταία εγγραφή
                        $last_record = 0;
                        foreach ($fields as $field) {
                            if ($last_record < $field->field_data->count()) {
                                $last_record = $field->field_data->count();
                            }
                        }
                        // Ετοίμασε τις εγγραφές στον πίνακα
                        foreach ($fields as $field) {
                            $data = null;
                            $field->field_data()->updateOrCreate(['school_id' => School::where('username', cas()->getAttribute('uid'))->first()->id, 'record' => $last_record], ['data' => $data]);
                        }
                        return redirect(route('report.edit.record', ['report' => $id, 'record' => $last_record]))->with('success', 'Η αναφορά ενημερώθηκε');
                    }

                    if ($next === 'exit') {
                        return redirect(route('report.index'))->with('success', 'Η αναφορά ενημερώθηκε');
                    }

                    if ($next === 'next') {
                        return redirect(route('report.edit.record', ['report' => $id, 'record' => $record + 1]))->with('success', 'Η αναφορά ενημερώθηκε');
                    }

                    if ($next === 'prev') {
                        return redirect(route('report.edit.record', ['report' => $id, 'record' => $record - 1]))->with('success', 'Η αναφορά ενημερώθηκε');
                    }

                    if (is_numeric($next) && is_int(intval($next))) {
                        return redirect(route('report.edit.record', ['report' => $id, 'record' => $next]))->with('success', 'Η αναφορά ενημερώθηκε');
                    }
                    else {
                        return redirect(route('report.index'))->with('success', 'Η αναφορά ενημερώθηκε');
                    }
                }

                return redirect(route('report.index'))->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            // Εφόσον ήρθαμε ως εδώ ο λογαριασμός δεν ανήκει σε σχολείο.
            // Επέστρεψε το view που μας επέστρεψε η συνάρτηση.
            return $access;
        }

        return redirect(route('report.index'))->with('error', 'Λάθος αναγνωριστικό φόρμας');
    }
}
