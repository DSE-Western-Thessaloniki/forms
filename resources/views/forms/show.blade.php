@extends('layouts.app')

@section('content')
    <br/>
    <a href="/forms" class="btn btn-secondary" role="button">Go Back</a>
    <h1>{{$form->title}}</h1>
    <div>
        {!!$form->body!!}
    </div>
    <hr/>
    <small>Written on {{$form->created_at}}</small>
    <hr/>
    <a href="/forms/{{$form->id}}/edit" class="btn btn-primary">Edit</a>

    <!-- The following lines are needed to be able to delete a form -->
    {!!Form::open(['action' => ['FormsController@destroy', $form->id], 'method' => 'POST', 'class' => 'float-right']) !!}
        {{Form::hidden('_method', 'DELETE')}}
        {{Form::submit('Delete', ['class' => 'btn btn-danger'])}}
    {!!Form::close()!!}
@endsection
