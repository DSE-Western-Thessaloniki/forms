@extends('layouts.app')

@section('content')
    <h1 class="text-center">{{__('Create Form')}}</h1>
    {!! Form::open(['action' => 'FormsController@store',
                    'method' => 'POST']) !!}
        <vform-component></vform-component>

        <br/>
        <div class="col-md-10 d-flex justify-content-end">
            {{Form::submit(__('Save'), ['class' => 'btn btn-primary'])}}
        </div>
    {!! Form::close() !!}

@endsection
