@extends('layouts.app')

@section('content')
    <h1 class="text-center">{{__('Edit Form')}}</h1>
    <form action="{{ route('forms.update', $form->id)}}" method="post">


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
            <button class="btn btn-primary" type="submit">{{ __('Save') }}</button>
        </div>
    </form>
@endsection
