<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldData;
use App\Models\School;
use Illuminate\Foundation\Auth\User as AuthUser;
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = Auth::guard('school')->user()->id;
        $school = School::find($id);
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
            return view('report.show')->with('form', $form);
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
        if ($form)
            return view('report.edit')->with('form', $form);
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
        $form = Form::with('form_fields')->find($id);
        $fields = $form->form_fields;
        foreach ($fields as $field) {
            $data = $request->input("f".$field->id);
            if (is_array($data)) {
                $data = json_encode($data);
            }
            $field->field_data()->updateOrCreate(['school_id' => Auth::guard('school')->user()->id], ['data' => $data]);
        }

        return redirect(route('report.index'))->with('success', 'Η αναφορά ενημερώθηκε');
    }
}
