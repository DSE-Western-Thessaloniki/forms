@extends('layouts.app')

@section('content')
    <h1>Forms</h1>
    <a class="nav-link" href="/forms/create">Create form</a>

    @if(count($forms) > 0)
        @foreach($forms as $form)
            <div class="card card-body bg-light">
                    <h3><a href="/forms/{{$form->id}}">{{$form->title}}</a></h3>
                    <small>Created on {{$form->created_at}} by {{ $form->user->name }}</small>

                    <div class="row align-items-center justify-content-start">
                        <div class="col">
                            <a href="/forms/{{$form->id}}/edit" class="btn btn-primary">Edit</a>
                        </div>

                        <div class="col">
                            <!-- The following lines are needed to be able to delete a form -->
                            {!!Form::open(['action' => ['FormsController@destroy', $form->id], 'method' => 'POST', 'class' => 'float-right']) !!}
                                {{Form::hidden('_method', 'DELETE')}}
                                {{Form::submit('Delete', ['class' => 'btn btn-danger'])}}
                            {!!Form::close()!!}
                        </div>
                    </div>
            </div>
        @endforeach
        {{$forms->links()}}
    @else
        <p>No forms found</p>
    @endif
@endsection
