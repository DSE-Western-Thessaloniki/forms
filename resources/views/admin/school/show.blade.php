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

                    <table class="table table-striped">
                        <tr>
                            <td>Όνομα χρήστη</td>
                            <td class="text-center">
                                {{ $school->username }}
                            </td>
                        </tr>

                        <tr>
                            <td>Όνομα</td>
                            <td class="text-center">
                                {{ $school->name }}
                            </td>
                        </tr>

                        <tr>
                            <td>Κωδικός</td>
                            <td class="text-center">
                                {{ $school->code }}
                            </td>
                        </tr>

                        <tr>
                            <td>E-mail</td>
                            <td class="text-center">
                                <pre>{{ $school->email }}</pre>
                            </td>
                        </tr>

                        <tr>
                            <td>Τηλέφωνο</td>
                            <td class="text-center">
                                <pre>{{ $school->telephone }}</pre>
                            </td>
                        </tr>

                        <tr>
                            <td>Ενεργός</td>
                            <td class="text-center">
                                @if ($school->active)
                                    Ναι
                                @else
                                    Όχι
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>Κατηγορίες</td>
                            <td class="text-center">
                                <ul>
                                @foreach ($school->categories as $category)
                                    <li>{{$category->name}}</li>
                                @endforeach
                                </ul>
                            </td>
                        </tr>

                        <tr>
                            <td class="col-2">
                                <a class="btn btn-danger" href="{{ route('admin.school.index') }}">Επιστροφή</a>
                            </td>
                            <td class="col d-flex justify-content-end">
                                <a class="btn btn-primary" href="{{ route('admin.school.edit', $school->id)}}">Επεξεργασία</a>
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
