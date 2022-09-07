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

                    <table class="table table-striped">
                        <tr>
                            <td>Όνομα</td>
                            <td class="text-center">
                                {{ $schoolcategory->name }}
                            </td>
                        </tr>

                        <tr>
                            <td>Id</td>
                            <td class="text-center">
                                {{ $schoolcategory->id }}
                            </td>
                        </tr>

                        <tr>
                            <td>Σχολικές μονάδες</td>
                            <td class="text-center">
                                <ul>
                                @foreach ($schoolcategory->schools as $school)
                                    {{ $school->name }}
                                @endforeach
                                </ul>
                            </td>
                        </tr>

                        <tr>
                            <td class="col-2">
                                <a class="btn btn-danger" href="{{ route('admin.school.schoolcategory.index') }}">Επιστροφή</a>
                            </td>
                            <td class="col d-flex justify-content-end">
                                <a class="btn btn-primary" href="{{ route('admin.school.schoolcategory.edit', $schoolcategory->id)}}">Επεξεργασία</a>
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
