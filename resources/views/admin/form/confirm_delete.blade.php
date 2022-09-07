@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Φόρμες</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="container">
                        <div class="card my-2 shadow-sm">
                            <div class="card-header">
                                Επιβεβαίωση διαγραφής φόρμας
                            </div>
                            <div class="card-body">
                                <p class="h3 text-danger">Είστε σίγουροι ότι θέλετε να διαγράψετε την φόρμα με τίτλο "{{ $form->title }}"; Θα διαγραφούν μαζί και όλα τα δεδομένα της φόρμας.</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.form.index') }}" class="btn btn-secondary">Άκυρο</a>
                                    @if(Auth::user()->roles->whereNotIn('name', ['User'])->count() && !(Auth::user()->roles->where('name', 'Author')->count() && Auth::user()->id != $form->user->id))
                                    <form action="{{ route('admin.form.destroy', $form->id)}}" method="post" class="float-right">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit" data-bs-toggle="tooltip" data-placement="top" title="Διαγραφή">@icon('fas fa-trash-alt') Διαγραφή</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
