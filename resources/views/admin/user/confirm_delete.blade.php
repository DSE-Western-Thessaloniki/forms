@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Χρήστες</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="container">
                        <div class="card my-2 shadow-sm">
                            <div class="card-header">
                                Επιβεβαίωση διαγραφής χρήστη
                            </div>
                            <div class="card-body">
                                <p class="h3 text-danger">Είστε σίγουροι ότι θέλετε να διαγράψετε τον χρήστη {{ $user->name }}; <b>Προσοχή! Αν ο χρήστης έχει δημιουργήσει φόρμες αυτές θα διαγραφούν μαζί με όλα τα δεδομένα τους!</b></p>
                                <div class="mt-4 d-flex justify-content-between">
                                    <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">Άκυρο</a>
                                    <form action="{{ route('admin.user.destroy', $user->id) }}" method="post" class="float-right">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit" data-bs-toggle="tooltip" data-placement="top" title="Διαγραφή">@icon('fas fa-trash-alt') Διαγραφή</button>
                                    </form>
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
