@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Αλλαγή Κωδικού</div>

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

                    <form action="{{ route('admin.user.change_password', $user->id) }}" method="post">

                    <div class="form-group row">
                        <label for="password" class="col-md-4 col-form-label text-md-right">Νέος κωδικός</label>

                        <div class="col-md-6">
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="password-confirm" class="col-md-4 col-form-label text-md-right">Επιβεβαίωση νέου κωδικού</label>

                        <div class="col-md-6">
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-2">
                            <a class="btn btn-danger" href="{{ route('admin.user.index') }}">Ακύρωση</a>
                        </div>
                        <div class="col-10 d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">Αποθήκευση</button>
                        </div>
                    </div>
                    @csrf
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
