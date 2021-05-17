@extends('layouts.admin.app')

@section('content')
    <h1>Σχολεία</h1>
    <a class="btn btn-primary my-2" href="{{ route('admin.school.create') }}">@icon('fas fa-plus') Δημιουργία σχολικής μονάδας</a>

    @if(count($schools) > 0)
        @foreach($schools as $school)
            <div class="card card-body bg-light">
                    <h3><a href="{{ route('admin.school.show', $school->id) }}">{{$school->username}}</a></h3>
                    <p>{{ $school->name }}</p>
                    <small>Τελευταία ενημέρωση από {{ $school->user->name }}</small>

                    <div class="row align-items-center justify-content-start pt-4">
                        <div class="col">
                            <a href="{{ route('admin.school.edit', $school->id) }}" class="btn btn-primary">Επεξεργασία</a>
                        </div>

                        <div class="col">
                            <form action="{{ route('admin.school.destroy', $school->id)}}" method="post" class="float-right">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Διαγραφή</button>
                            </form>
                        </div>
                    </div>
            </div>
        @endforeach
        {{$schools->links()}}
    @else
        <p>Δεν βρέθηκαν σχολικές μονάδες</p>
    @endif
@endsection
