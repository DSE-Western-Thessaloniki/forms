@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Χρήστες</div>

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

                    <form action="{{ route('admin.user.update', $user->id) }}" method="post">

                    <div class="form-group row mb-3">
                        <label for="name" class="col-md-4 col-form-label text-md-right">Όνομα</label>

                        <div class="col-md-6">
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $user->name }}" required autocomplete="name" autofocus>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="username" class="col-md-4 col-form-label text-md-right">Όνομα χρήστη</label>

                        <div class="col-md-6">
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ $user->username }}" required autocomplete="username">

                            @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="email" class="col-md-4 col-form-label text-md-right">Διεύθυνση E-Mail</label>

                        <div class="col-md-6">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" required autocomplete="email">

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <div class="col-md-4"></div>
                        <div class="col-md-6 offset-sm-1">
                            <div class="form-check">
                            @php
                                if (Auth::user()->isAdministrator()) {
                                    $disabled = "";
                                }
                                else {
                                    $disabled = "disabled='disabled'";
                                }
                            @endphp
                            @if ($user->active)
                                <input type="checkbox" class="form-check-input" name="active" id="active" value="1" checked="checked" {{ $disabled }}>
                            @else
                                <input type="checkbox" class="form-check-input" name="active" id="active" value="1" {{ $disabled }}>
                            @endif
                            <label for="active" class="form-check-label">Ενεργός</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <div class="col-md-4"></div>
                        <div class="col-md-6 offset-sm-1">
                            <div class="form-check">
                                <input id="password-reset" type="checkbox" class="form-check-input" name="password_reset" value="1" @checked(old('password_reset', $user->password_reset))>
                                <label for="password-reset" class="form-check-label">Αλλαγή του κωδικού μετά την πρώτη είσοδο</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-3 justify-content-center">
                        <div class="col-10">
                            @php
                            $roles = array();
                            foreach($user->roles as $role) {
                                array_push($roles, $role->name);
                            }
                            @endphp
                            <rolecomponent
                                current_roles="{{ json_encode($roles) }}"
                                disabled="{{ Auth::user()->isAdministrator() ? '' : 'disabled' }}"
                            >
                            </rolecomponent>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <div class="col-2">
                            @can('viewAny', App\Models\User::class)
                                <a class="btn btn-danger" href="{{ route('admin.user.index') }}">Ακύρωση</a>
                            @else
                                <a class="btn btn-danger" href="{{ route('admin.index') }}">Ακύρωση</a>
                            @endcan
                        </div>
                        <div class="col d-flex justify-content-end">
                            @method('PUT')
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
