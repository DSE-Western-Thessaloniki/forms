@extends('layouts.app')

@section('content')
    <h1>Φόρμες</h1>

    @if(count($forms) > 0)
        @foreach($forms as $form)
            <div class="card card-body bg-light">
                    <h3><a href="{{ route('reports.show', $form->id) }}">{{$form->title}}</a></h3>
                    <p>{{ $form->notes }}</p>
                    <small>Δημιουργήθηκε στις {{$form->created_at}} από τον/την {{ $form->user->name }}</small>

                    <div class="row align-items-center justify-content-start pt-4">
                        <div class="col">
                            <a href="{{ route('reports.edit', $form->id) }}" class="btn btn-primary">Συμπλήρωση</a>
                        </div>

                        <div class="col">
                        </div>
                    </div>
            </div>
        @endforeach
        {{$forms->links()}}
    @else
        <p>Δεν βρέθηκαν φόρμες</p>
    @endif
@endsection
