@extends('layouts.app')

@section('content')

<div class="container">
    <form action="{{ route('login') }}" method="POST">
        <label for="school_id">Αναγνωριστικό σχολείου:</label>
        <input class="form-control" type="text" name="school_id">
        @csrf
    </form>
</div>

@endsection
