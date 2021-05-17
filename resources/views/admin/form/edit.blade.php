@extends('layouts.admin.app')

@section('content')
    <h1 class="text-center">Επεξεργασία φόρμας</h1>
    <form action="{{ route('admin.form.update', $form->id) }}" method="post">


        <vform-component
            :parse=true
            :parseobj="{{ $form->formFields->toJson() }}"
            parsetitle="{{ $form->title }}"
            parsenotes="{{ $form->notes }}"
        >
        </vform-component>

        <br/>
        <div class="col-md-10 d-flex justify-content-end">
            @csrf
            @method('PUT')
            <button class="btn btn-primary" type="submit">Αποθήκευση</button>
        </div>
    </form>
@endsection
