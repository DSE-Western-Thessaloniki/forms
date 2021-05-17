@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Πίνακας ελέγχου</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row control-panel">
                        @if(Auth::user()->isAdministrator())

                        <a class="btn btn-light col-md-4" href="{{ route('admin.user.index') }}">
                            <div class="card">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <h1 class="display-3 pt-3">@icon('fas fa-users')</h1>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title">Χρήστες</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a class="btn btn-light col-md-4" href="{{ route('admin.school.index') }}">
                            <div class="card">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <h1 class="display-3 pt-3">@icon('fas fa-school')</h1>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title">Σχολεία</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endif

                        <a class="btn btn-light col-md-4" href="{{ route('admin.forms.index') }}">
                            <div class="card">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <h1 class="display-3 pt-3">@icon('fas fa-file-medical-alt')</h1>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title">Φόρμες</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
