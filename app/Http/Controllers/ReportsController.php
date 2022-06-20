<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    // Κρατάει το μοντέλο της σχολικής μονάδας μετά τον έλεγχο από την συνάρτηση school_has_access
    private $school_model_cache = null;

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
        $school = School::where('username', cas()->getAttribute('uid'))
            ->orWhere('email', cas()->getAttribute('mail'))
            ->first();

        if (!$school) { // Αν ο λογαριασμός δεν αντιστοιχεί σε σχολική μονάδα
            $this->school_model_cache = null;
            Log::warning("Το uid:".cas()->getAttribute('uid')." και το email:".cas()->getAttribute('mail')." δεν αντιστοιχούν σε λογαριασμό.");
            return view('pages.deny_access');
        }

        $categories = $school->categories;
        $form_categories = $form->school_categories;
        $in_category = false;
        foreach ($categories as $category) {
            if ($form_categories->contains($category))
                $in_category = true;
        }

        $this->school_model_cache = $school;
        return ($form->schools()->where('school_id', $school->id)->count() > 0 || $in_category);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $school = School::where('username', cas()->getAttribute('uid'))
            ->orWhere('email', cas()->getAttribute('mail'))
            ->first();
        if ($school) {
            $categories = $school->categories->pluck('id');
            $forms = Form::where('active', true)
                ->where(function($query) use ($school, $categories) { // Προσθήκη παρένθεσης
                    $query->whereHas('schools', function ($q) use ($school) {
                        $q->where('school_id', $school->id);
                    })
                    ->when($categories, function ($q) use ($categories) { // Αν το σχολείο ανήκει σε μια τουλάχιστον κατηγορία
                        $q->orWhereHas('school_categories', function ($q) use ($categories) {
                            $q->whereIn('school_category_id', $categories);
                        });
                    });
                })
                ->with('user')
                ->orderBy('created_at', 'desc')->paginate(15);
            return view('report.index')->with('forms', $forms);
        }

        Log::warning("Το uid:".cas()->getAttribute('uid')." και το email:".cas()->getAttribute('mail')." δεν αντιστοιχούν σε λογαριασμό.");
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
        $form = Form::where('active', true)->find($id);
        if ($form) {
            $access = $this->school_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $record_data = $form->data()->where('school_id', $this->school_model_cache->id)->where('record', 0)->get();

                    $data_dict = array();
                    foreach ($record_data as $item) {
                        $data_dict[$item->form_field_id] = $item->data;
                    }

                    return view('report.show')
                        ->with('form', $form)
                        ->with('data_dict', $data_dict)
                        ->with('school', $this->school_model_cache);
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  int  $record
     * @return \Illuminate\Http\Response
     */
    public function showRecord($id, $record)
    {
        $form = Form::where('active', true)->find($id);
        if ($form) {
            $access = $this->school_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $record_data = $form->data()->where('school_id', $this->school_model_cache->id)->where('record', $record)->get();

                    $data_dict = array();
                    foreach ($record_data as $item) {
                        $data_dict[$item->form_field_id] = $item->data;
                    }

                    return view('report.show')
                        ->with('form', $form)
                        ->with('record', $record)
                        ->with('data_dict', $data_dict)
                        ->with('school', $this->school_model_cache);
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
        $form = Form::where('active', true)->find($id);
        if ($form) {
            $access = $this->school_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    $record_data = $form->data()->where('school_id', $this->school_model_cache->id)->where('record', 0)->get();

                    $data_dict = array();
                    foreach ($record_data as $item) {
                        $data_dict[$item->form_field_id] = $item->data;
                    }

                    return view('report.edit')
                        ->with('form', $form)
                        ->with('data_dict', $data_dict)
                        ->with('school', $this->school_model_cache);
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
        $form = Form::where('active', true)->find($id);
        if ($form) {
            $access = $this->school_has_access($form);
            if (is_bool($access)) {
                if ($access) {
                    if ($form->multiple) {
                        $record_data = $form->data()->where('school_id', $this->school_model_cache->id)->where('record', $record)->get();

                        $data_dict = array();
                        foreach ($record_data as $item) {
                            $data_dict[$item->form_field_id] = $item->data;
                        }

                        return view('report.edit')
                            ->with('form', $form)
                            ->with('record', $record)
                            ->with('data_dict', $data_dict)
                            ->with('school', $this->school_model_cache);
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
        $form = Form::with('form_fields')->where('active', true)->find($id);
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
                        $field->field_data()
                            ->updateOrCreate(
                                ['school_id' => School::where('username', cas()->getAttribute('uid'))->orWhere('email', cas()->getAttribute('mail'))->first()->id],
                                ['data' => $data]
                            );
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
    public function updateRecord(Request $request, $id, int $record, $next)
    {
        $form = Form::with('form_fields')->where('active', true)->find($id);
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
                        $field->field_data()->updateOrCreate(['school_id' => School::where('username', cas()->getAttribute('uid'))->orWhere('email', cas()->getAttribute('mail'))->first()->id, 'record' => $record], ['data' => $data]);
                    }

                    // Που πάμε τώρα;
                    if ($next === 'new') {
                        // Βρες την τελευταία εγγραφή
                        $last_record = 0;
                        foreach ($fields as $field) {
                            if ($last_record < $field->field_data->where('school_id', $this->school_model_cache->id)->count()) {
                                $last_record = $field->field_data->where('school_id', $this->school_model_cache->id)->count();
                            }
                        }
                        // Ετοίμασε τις εγγραφές στον πίνακα
                        foreach ($fields as $field) {
                            $data = null;
                            $field->field_data()->updateOrCreate(['school_id' => School::where('username', cas()->getAttribute('uid'))->orWhere('email', cas()->getAttribute('mail'))->first()->id, 'record' => $last_record], ['data' => $data]);
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
