@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Κατηγορίες</div>

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

                    <form action={{ route('admin.school.schoolcategory.store') }} method='post'>

                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">Όνομα</label>

                        <div class="col-md-6">
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="off" autofocus>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-2">
                            <a class="btn btn-danger" href="{{ route('admin.school.schoolcategory.index') }}">Ακύρωση</a>
                        </div>
                        <div class="col-10 d-flex justify-content-end">
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
