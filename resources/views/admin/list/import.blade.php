@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Λίστες</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if ($errors->any())
                    <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    </div><br />
                    @endif

                    <div class="container">
                        <div class="row">
                            <div class="col-12">

                                <div class="card text-white bg-info">
                                    <div class="card-header">
                                        Οδηγίες για εισαγωγή αρχείου λίστας
                                    </div>
                                    <div class="card-body">
                                        Το αρχείο για εισαγωγή πρέπει να είναι
                                        ένα csv αρχείο σε κωδικοποίηση UTF8 και
                                        το οποίο θα έχει έναν πίνακα με μία
                                        μοναδική στήλη με τα δεδομένα. Το κελί
                                        στην πρώτη γραμμή και πρώτη στήλη
                                        θα χρησιμοποιηθεί ως όνομα της
                                        λίστας. Στην περίπτωση που το αρχείο
                                        έχει παραπάνω από μία στήλες αυτές θα
                                        πρέπει να είναι οριοθετημένες με τον
                                        χαρακτήρα του ελληνικού ερωτηματικού (;)
                                        και οι υπόλοιπες στήλες δεν λαμβάνονται
                                        υπόψη.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <form enctype="multipart/form-data" action={{ route('admin.list.import') }} method='post'>

                                    <div class="form-group row mb-3">
                                        <label for="csvfile" class="col-md-auto col-form-label text-md-right">Αρχείο για εισαγωγή</label>

                                        <div class="col-md">
                                            <input accept=".csv,text/csv" id="csvfile" type="file" class="form-control @error('csvfile') is-invalid @enderror" name="csvfile" value="{{ old('csvfile') }}" required autocomplete="csvfile" autofocus>

                                            @error('csvfile')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row mb-3">
                                        <div class="col-2">
                                            <a class="btn btn-danger" href="{{ route('admin.list.index') }}">Ακύρωση</a>
                                        </div>
                                        <div class="col d-flex justify-content-end">
                                            <button class='btn btn-primary' type='submit'>Εισαγωγή</a>
                                        </div>
                                    </div>
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
