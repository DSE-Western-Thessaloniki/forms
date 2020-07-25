@extends('layouts.app')

@section('content')
    <h1>Forms</h1>
    <a class="nav-link" href="/forms/create">Create form</a>

    @if(count($forms) > 0)
        @foreach($forms as $form)
            <div class="card card-body bg-light">
                <h3><a href="/forms/{{$form->id}}">{{$form->title}}</a></h3>
                <small>Written on {{$form->created_at}}</small>
            </div>
        @endforeach
        {{$forms->links()}}
    @else
        <p>No forms found</p>
    @endif
@endsection
