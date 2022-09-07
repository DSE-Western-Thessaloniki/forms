@extends('layouts.app')

@section('content')
    <div class="jumbotron text-center">
        <h1>Φόρμες {{ config('app.service_name') }}</h1>
        <p>Καλωσορίσατε στην εφαρμογή καταχώρησης στοιχείων της {{ config('app.service_name') }}</h1>.</p>
        @if (cas()->isAuthenticated())
            <p><a class="btn btn-primary btn-lg" href="{{ route('report.index') }}" role="Button">Εμφάνιση διαθέσιμων φορμών για συμπλήρωση</a></p>
        @else
            <p><a class="btn btn-primary btn-lg" href="{{ route('login') }}" role="Button">Σύνδεση</a></p>
        @endif
    </div>
@endsection
