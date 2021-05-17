@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Χρήστες</div>

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

                    <table class="table table-striped">
                        <tr>
                            <td>Όνομα χρήστη</td>
                            <td class="text-center">
                                {{ $user->username }}
                            </td>
                        </tr>

                        <tr>
                            <td>Όνομα</td>
                            <td class="text-center">
                                {{ $user->name }}
                            </td>
                        </tr>

                        <tr>
                            <td>E-mail</td>
                            <td class="text-center">
                                <pre>{{ $user->email }}</pre>
                            </td>
                        </tr>

                        <tr>
                            <td>Ενεργός</td>
                            <td class="text-center">
                                @if ($user->active)
                                    Ναι
                                @else
                                    Όχι
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>Ρόλοι</td>
                            <td>
                                <ul class="role-list">
                                    @foreach ($user->roles as $role)
                                    <li>{{ $role->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-2">
                                <a class="btn btn-danger" href="{{ route('admin.user.index') }}">{{ __('Back') }}</a>
                            </td>
                            <td class="col-10 d-flex justify-content-end">
                                <a class="btn btn-primary" href="{{ route('admin.user.edit', $user->id)}}">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    </table>
                </div>
                @else
                    Δεν επιτρέπεται η πρόσβαση
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
