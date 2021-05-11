@extends('layouts.app')

@section('content')
    <h1>Φόρμες</h1>
    <a class="nav-link" href="{{ route('admin.forms.create') }}">Δημιουργία φόρμας</a>

    @if(count($forms) > 0)
        @foreach($forms as $form)
            <div class="card card-body bg-light">
                    <h3><a href="{{ route('admin.forms.show', $form->id) }}">{{$form->title}}</a></h3>
                    <p>{{ $form->notes }}</p>
                    <small>Δημιουργήθηκε στις {{$form->created_at}} από τον/την {{ $form->user->name }}</small>

                    <div class="row align-items-center justify-content-start pt-4">
                        <div class="col">
                            <a href="{{ route('admin.forms.edit', $form->id) }}" class="btn btn-primary">Επεξεργασία</a>
                        </div>

                        <div class="col">
                            <form action="{{ route('admin.forms.destroy', $form->id)}}" method="post" class="float-right">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Διαγραφή</button>
                            </form>
                        </div>
                    </div>
            </div>
        @endforeach
        {{$forms->links()}}
    @else
        <p>Δεν βρέθηκαν φόρμες</p>
    @endif
@endsection
