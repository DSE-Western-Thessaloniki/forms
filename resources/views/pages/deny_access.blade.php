@extends('layouts.app')

@section('content')
    <div class="jumbotron text-center bg-warning">
        <h1>Σφάλμα</h1>
        <p>Δεν έχετε πρόσβαση στο σύστημα με αυτόν τον λογαριασμό. Παρακαλούμε πατήστε παρακάτω για αποσύνδεση.</p>
        <a href="{{ route('logout') }}" class='btn btn-primary'>Αποσύνδεση</a>
    </div>


@endsection
