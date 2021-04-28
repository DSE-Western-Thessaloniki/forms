@extends('layouts.app')

@section('content')
    <br/>
    <a href="/forms" class="btn btn-secondary" role="button">Go Back</a>
    <h1>{{$form->title}}</h1>
    <h3>{{$form->notes}}</h3>
    <hr/>
    <div class="card">
        <div class="card-header">
            Preview
        </div>
        <div class="card-body">
            @foreach ($form->formfields as $field)
                @include('inc.formfields')
            @endforeach
        </div>
    </div>
    <small>Written on {{$form->created_at}}</small>
    <hr/>
    <a href="/forms/{{$form->id}}/edit" class="btn btn-primary">Edit</a>

    <!-- The following lines are needed to be able to delete a form -->
    <form action="{{ route('forms.destroy', $form->id)}}" method="post" class="float-right">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">{{ __('Delete') }}</button>
    </form>
@endsection
