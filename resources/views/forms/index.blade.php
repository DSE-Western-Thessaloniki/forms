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
                            <form action="{{ route('forms.destroy', $form->id)}}" method="post" class="float-right">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
            </div>
        @endforeach
        {{$forms->links()}}
    @else
        <p>No forms found</p>
    @endif
@endsection
