@extends('layouts.app')

@section('content')
    <br/>
    <a href="/forms" class="btn btn-secondary" role="button">Πίσω</a>
    <h1>{{$form->title}}</h1>
    <h3>{{$form->notes}}</h3>
    <hr/>
    <div class="card">
        <div class="card-header">
            Προεπισκόπιση
        </div>
        <div class="card-body">
            @foreach ($form->formfields as $field)
                @include('inc.formfields')
            @endforeach
        </div>
    </div>
    <small>Δημιουργήθηκε στις {{$form->created_at}}</small>
    <hr/>
    <a href="/forms/{{$form->id}}/edit" class="btn btn-primary">Επεξεργασία</a>

    <!-- The following lines are needed to be able to delete a form -->
    <form action="{{ route('forms.destroy', $form->id)}}" method="post" class="float-right">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">Διαγραφή</button>
    </form>
@endsection
