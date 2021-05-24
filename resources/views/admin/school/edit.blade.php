@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Σχολεία</div>

                @if(Auth::user()->isAdministrator())
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

                    <form action={{ route('admin.school.update', $school->id) }} method='post'>

                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">Όνομα</label>

                        <div class="col-md-6">
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $school->name }}" required autocomplete="name" autofocus>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="username" class="col-md-4 col-form-label text-md-right">Όνομα χρήστη</label>

                        <div class="col-md-6">
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ $school->username }}" required autocomplete="username">

                            @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="code" class="col-md-4 col-form-label text-md-right">Κωδικός Σχολικής Μονάδας</label>

                        <div class="col-md-6">
                            <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ $school->code }}" required autocomplete="code">

                            @error('code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="email" class="col-md-4 col-form-label text-md-right">E-Mail</label>

                        <div class="col-md-6">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $school->email }}" required autocomplete="email">

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="category" class="col-md-4 col-form-label text-md-right">Κατηγορία</label>

                        <div class="col-md-6">
                            <pillbox
                                :options="{{ json_encode($categories) }}"
                                name="category"
                                value="{{ $category_string }}"
                                >
                            </pillbox>

                            @error('category')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-md-4 col-form-label text-md-right">Ενεργός</label>

                        <div class="col-md-6">
                            <div class="form-check">
                                @if ($school->active)
                                    <input id="active" type="checkbox" class="form-check-input" name="active" value="1" checked="checked">
                                @else
                                    <input id="active" type="checkbox" class="form-check-input" name="active" value="1">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-2">
                            <a class="btn btn-danger" href="{{ route('admin.school.index') }}">Ακύρωση</a>
                        </div>
                        <div class="col-10 d-flex justify-content-end">
                            @method('PUT')
                            <button class='btn btn-primary' type='submit'>Αποθήκευση</a>
                        </div>
                    </div>
                    @csrf
                    </form>

                </div>
                @else
                Δεν επιτρέπεται η πρόσβαση
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
