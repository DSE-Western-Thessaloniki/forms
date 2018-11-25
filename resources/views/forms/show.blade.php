@extends('layouts.app')

@section('content')
    <br/>
    <a href="/forms" class="btn btn-primary" role="button">Go Back</a>
    <h1>{{$form->title}}</h1>
    <div>
        {!!$form->body!!}
    </div>
    <hr/>
    <small>Written on {{$form->created_at}}</small>
@endsection
