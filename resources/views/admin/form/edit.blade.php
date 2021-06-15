@extends('layouts.admin.app')

@section('content')
    <h1 class="text-center">Επεξεργασία φόρμας</h1>
    <div class="container">
        <form action="{{ route('admin.form.update', $form->id) }}" method="post">


            <vform-component
                :parse=true
                :parseobj="{{ $form->form_fields->toJson() }}"
                parsetitle="{{ $form->title }}"
                parsenotes="{{ $form->notes }}"
                :schools="{{ json_encode($schools) }}"
                :categories="{{ json_encode($categories) }}"
                :school_selected_values="{{ json_encode($school_selected_values) }}"
                :category_selected_values="{{ json_encode($category_selected_values) }}"
            >
            >
            </vform-component>

            <div class="form-group row mt-5">
                <div class="col-2">
                    <a class="btn btn-danger" href="{{ route('admin.form.index') }}">Ακύρωση</a>
                </div>
                <div class="col-10 d-flex justify-content-end">
                    @csrf
                    @method('PUT')
                    <button class="btn btn-primary" type="submit">Αποθήκευση</button>
                </div>
            </div>
        </form>
    </div>
@endsection
