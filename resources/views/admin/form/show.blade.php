@extends('layouts.admin.app')

@section('content')
    <div class="btn-group">
        <a href="{{route('admin.form.index')}}" class="btn btn-secondary" role="button">@icon('fas fa-long-arrow-alt-left') Πίσω</a>
        <a href="{{ route('admin.form.edit', $form->id) }}" class="btn btn-primary">@icon('fas fa-edit') Επεξεργασία</a>
        <button class="btn btn-danger" type="submit" form="delete">@icon('fas fa-trash-alt') Διαγραφή</button>
    </div>
    <h1>{{$form->title}}</h1>
    <h3 class="pre-wrap">{{$form->notes}}</h3>
    <hr/>
    <div class="card">
        <div class="card-header">
            Προεπισκόπιση
        </div>
        <div class="card-body">
            @foreach ($form->form_fields as $field)
                @include('inc.formfields')
            @endforeach
        </div>
    </div>
    <small>Δημιουργήθηκε στις {{$form->created_at}}</small>
    <hr/>

    <!-- The following lines are needed to be able to delete a form -->
    <form action="{{ route('admin.form.destroy', $form->id)}}" id="delete" method="post" class="float-right">
        @csrf
        @method('DELETE')
    </form>
@endsection
