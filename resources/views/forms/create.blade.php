@extends('layouts.app')

@section('content')
    <h1 class="text-center">{{__('Create Form')}}</h1>
    {!! Form::open(['action' => 'FormsController@store',
                    'method' => 'POST']) !!}
        <vform-component></vform-component>
        <!--<div class="form-group">
            {{Form::label('title', 'Title')}}
            {{Form::text('title', '', ['class' => 'form-control', 'placeholder' => 'Title'])}}
        </div>
        <div class="form-group">
            {{Form::label('body', 'Body')}}
            {{Form::textarea('body', '', ['id' => 'article-ckeditor', 'class' => 'form-control', 'placeholder' => 'Body text'])}}
        </div>-->
        <br/>
        <div class="col-md-10 d-flex justify-content-end">
            {{Form::submit(__('Save'), ['class' => 'btn btn-primary'])}}
        </div>
    {!! Form::close() !!}

@endsection
