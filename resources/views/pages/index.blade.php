@extends('layouts.app')

@section('content')
    <div class="jumbotron text-center">
        <h1>Φόρμες {{ config('app.service_name') }}</h1>
        <p>Καλωσορίσατε στην εφαρμογή καταχώρησης στοιχείων της {{ config('app.service_name') }}</h1>.</p>
        @unless (Auth::check())
            <p><a class="btn btn-primary btn-lg" href="{{ route('login') }}" role="Button">Σύνδεση</a></p>
        @endunless
    </div>
@endsection
