@extends('layouts.admin.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Εκπαιδευτικοί</div>

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

                        <form action={{ route('admin.teacher.update', $teacher) }} method='post'>
                            @method('PUT')

                            <div class="form-group row mb-3">
                                <label for="surname" class="col-md-4 col-form-label text-md-right">Επώνυμο</label>

                                <div class="col-md-6">
                                    <input id="surname" type="text"
                                        class="form-control @error('surname') is-invalid @enderror" name="surname"
                                        value="{{ old('surname') ?? $teacher->surname }}" required autocomplete="surname"
                                        autofocus>

                                    @error('surname')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Όνομα</label>

                                <div class="col-md-6">
                                    <input id="name" type="text"
                                        class="form-control @error('name') is-invalid @enderror" name="name"
                                        value="{{ old('name') ?? $teacher->name }}" required autocomplete="name" autofocus>

                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="am" class="col-md-4 col-form-label text-md-right">ΑΜ</label>

                                <div class="col-md-6">
                                    <input id="am" type="text"
                                        class="form-control @error('am') is-invalid @enderror" name="am"
                                        value="{{ old('am') ?? $teacher->am }}" required autocomplete="am">

                                    @error('am')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="afm" class="col-md-4 col-form-label text-md-right">ΑΦΜ</label>

                                <div class="col-md-6">
                                    <input id="afm" type="text"
                                        class="form-control @error('afm') is-invalid @enderror" name="afm"
                                        value="{{ old('afm') ?? $teacher->afm }}" required autocomplete="afm">

                                    @error('afm')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <div class="col-md-4 col-form-label"></div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        @if ($teacher->active)
                                            <input id="active" type="checkbox" class="form-check-input" name="active"
                                                value="1" checked="checked">
                                        @else
                                            <input id="active" type="checkbox" class="form-check-input" name="active"
                                                value="1">
                                        @endif
                                        <label class="form-check-label" for="active">Ενεργός</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <div class="col-2">
                                    <a class="btn btn-danger" href="{{ route('admin.teacher.index') }}">Ακύρωση</a>
                                </div>
                                <div class="col d-flex justify-content-end">
                                    <button class='btn btn-primary' type='submit'>Αποθήκευση</a>
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
