@extends('layouts.admin.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Κατηγορίες</div>

                    @if (Auth::user()->isAdministrator())
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

                            <form action={{ route('admin.school.schoolcategory.store') }} method='post'>

                                <p>Στην παρακάτω φόρμα προαιρετικά μπορούν να εισαχθούν οι κωδικοί των σχολείων που θα
                                    ενταχθούν στη συγκεκριμένη κατηγορία.</p>
                                <div class="form-group row mb-3">
                                    <label for="name" class="col-md-4 col-form-label text-md-right">Όνομα</label>

                                    <div class="col-md-6">
                                        <input id="name" type="text"
                                            class="form-control @error('name') is-invalid @enderror" name="name"
                                            value="{{ old('name') }}" required autocomplete="off" autofocus>

                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <label for="schools" class="col-md-4 col-form-label text-md-right">Σχολεία
                                        (προαιρετικό)</label>

                                    <div class="col-md-6">
                                        <textarea id="schools" class="form-control @error('schools') is-invalid @enderror" name="schools"
                                            value="{{ old('schools') }}" autocomplete="off" placeholder="Πχ. 1901901,1901902,...">
                            </textarea>

                                        @error('schools')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <div class="col-2">
                                        <a class="btn btn-danger"
                                            href="{{ route('admin.school.schoolcategory.index') }}">Ακύρωση</a>
                                    </div>
                                    <div class="col d-flex justify-content-end">
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
