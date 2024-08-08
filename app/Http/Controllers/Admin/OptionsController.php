<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOptionRequest;
use App\Models\Option;

class OptionsController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Option::class, 'option');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $options = Option::where('name', '!=', 'first_run')->get();

        return view('admin.option.index', compact('options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOptionRequest $request)
    {
        $allow_teacher_login = $request->input('allow_teacher_login');
        $allow_all_teachers = $request->input('allow_all_teachers');
        if ($allow_teacher_login == '1') {
            $option = Option::where('name', 'allow_teacher_login')->first();
            if ($allow_teacher_login === '1') {
                $option->value = '1';
            } else {
                $option->value = '0';
            }
            $option->save();

            $option = Option::where('name', 'allow_all_teachers')->first();
            if ($allow_all_teachers === '1') {
                $option->value = '1';
            } else {
                $option->value = '0';
            }
            $option->save();
        } else {
            $option = Option::where('name', 'allow_teacher_login')->first();
            $option->value = '0';
            $option->save();
        }

        return redirect(route('admin.options.index'))->with('status', 'Οι ρυθμίσεις αποθηκεύτηκαν!');
    }
}
