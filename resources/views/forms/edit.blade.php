@extends('layouts.app')

@section('content')
    <h1 class="text-center">{{__('Edit Form')}}</h1>
    {!! Form::open(['action' => ['FormsController@update', $form->id],
                    'method' => 'POST']) !!}


        <vform-component
            :parse=true
            :parseobj="{{ $form->formFields->toJson() }}"
            parsetitle="{{ $form->title }}"
            parsenotes="{{ $form->notes }}"
        >
        </vform-component>

        <br/>
        <div class="col-md-10 d-flex justify-content-end">
            {{Form::hidden('_method', 'PUT')}}
            {{Form::submit(__('Save'), ['class' => 'btn btn-primary'])}}
        </div>
    {!! Form::close() !!}
@endsection
