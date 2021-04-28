@extends('layouts.app')

@section('content')
    <div class="jumbotron text-center">
        <h1>Φόρμες ΔΔΕ Δυτ. Θεσσαλονίκης</h1>
        <p>Καλωσορίσατε στην εφαρμογή καταχώρησης στοιχείων της ΔΔΕ Δυτ. Θεσσαλονίκης.</p>
        @unless (Auth::check())
            <p><a class="btn btn-primary btn-lg" href="/login" role="Button">Σύνδεση</a></p>
        @endunless
    </div>
@endsection
