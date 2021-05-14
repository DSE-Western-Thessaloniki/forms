@extends('layouts.app')

@section('content')

<div class="container">
    <form action="{{ route('login') }}" method="POST">
        <label for="school_id">Αναγνωριστικό σχολείου:</label>
        <input class="form-control" type="text" name="school_id" value="999">
        <label for="school_name">Όνομα σχολείου:</label>
        <input class="form-control" type="text" name="school_name" value="Δοκιμαστική σχολική μονάδα">
        <button class="btn btn-primary mt-3" type="submit">Σύνδεση</button>
        @csrf
    </form>
</div>

@endsection
