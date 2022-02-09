@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Σχολεία</div>

                <div class="card-body">
                    @if(count($categories) == 0)
                        <p>Πρέπει πρώτα να δημιουργηθούν κατηγορίες σχολικών μονάδων.</p>
                        <div class="btn-toolbar pb-2" role="toolbar">
                            <div class="btn-group mr-2">
                                <a class="btn btn-success" href="{{ route('admin.school.schoolcategory.index')}}">
                                    @icon('fas fa-list') Διαχείριση κατηγοριών
                                </a>
                            </div>
                        </div>
                    @else
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

                        <form action={{ route('admin.school.store') }} method='post'>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Όνομα</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

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
                                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username">

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
                                <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" required autocomplete="code">

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
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="telephone" class="col-md-4 col-form-label text-md-right">Τηλέφωνο</label>

                            <div class="col-md-6">
                                <input id="telephone" type="text" class="form-control @error('telephone') is-invalid @enderror" name="telephone" value="{{ old('telephone') }}" required autocomplete="telephone">

                                @error('telephone')
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
                                    @if(old('category'))
                                    value="{{ old('category') }}"
                                    @endif
                                    >
                                </pillbox>

                                @error('category')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-2">
                                <a class="btn btn-danger" href="{{ route('admin.school.index') }}">Ακύρωση</a>
                            </div>
                            <div class="col-10 d-flex justify-content-end">
                                <button class='btn btn-primary' type='submit'>Αποθήκευση</a>
                            </div>
                        </div>
                        @csrf
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
