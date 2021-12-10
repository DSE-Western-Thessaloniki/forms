@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Σχολεία</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="container-fluid">
                        <div class="btn-toolbar pb-2" role="toolbar">
                            <div class="btn-group mr-2">
                                <a class="btn btn-primary" href="{{ route('admin.school.create')}}">
                                    @icon('fas fa-plus') Δημιουργία σχολικής μονάδας
                                </a>
                                <a class="btn btn-success" href="{{ route('admin.school.schoolcategory.index')}}">
                                    @icon('fas fa-list') Διαχείριση κατηγοριών
                                </a>
                                <a class="btn btn-secondary" href="{{ route('admin.school.import')}}">
                                    @icon('fas fa-file-csv') Εισαγωγή δεδομένων σχολικών μονάδων
                                </a>
                            </div>
                        </div>

                        <div class="row justify-content-md-center">
                                @forelse($schools as $school)
                                <div class="col-xl-4">
                                    <div class="card my-2 shadow">
                                        @if($school->active)
                                        <div class="card-header">
                                        @else
                                        <div class="card-header bg-danger text-white">
                                        @endif
                                            <strong>{{ $loop->iteration + $schools->firstItem() - 1 }}. {{$school->name}}</strong>
                                            @if(!$school->active)
                                            <em>(Ανενεργή)</em>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-auto d-flex flex-column flex-grow-1">
                                                    <div>@icon('fas fa-user') Όνομα χρήστη: {{$school->username}}</div>
                                                    <div>@icon('fas fa-school') Κωδικός ΥΠΑΙΘ: {{$school->code}}</div>
                                                    <div>@icon('fas fa-envelope') E-mail: {{$school->email}}</div>
                                                    <div class="flex-grow-1">
                                                        @icon('fas fa-tags') Κατηγορίες:
                                                        @foreach ($school->categories as $category)
                                                        <span class="rounded bg-warning p-1">@icon('fas fa-tag') {{$category->name}}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="col-lg-auto">
                                                    @if(Auth::user()->roles->where('name', 'Administrator')->count() > 0)
                                                        <a href="{{ route('admin.school.edit', $school) }}" class="btn btn-primary m-1">@icon('fas fa-edit') Επεξεργασία</a><br/>
                                                    @endif
                                                    @if(Auth::user()->roles->where('name', 'Administrator')->count() > 0)
                                                        <form action="{{ route('admin.school.destroy', $school->id)}}" method="post">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger m-1" type="submit">@icon('fas fa-trash-alt') Διαγραφή</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                    Δεν βρέθηκαν σχολικές μονάδες
                                @endforelse
                        </div>
                        <div class="row justify-content-md-center">
                            {{ $schools->links() }}
                        </div>
                    </div>
                    {{-- <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Όνομα σχολείου</th>
                                    <th>Όνομα χρήστη</th>
                                    <th>Κωδικός</th>
                                    <th>E-mail</th>
                                    <th>Κατηγορίες</th>
                                    <th>Ενεργό</th>
                                    <th>Ενημερώθηκε από</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($schools as $school)
                            <tr>
                                <td>{{ $loop->iteration + $schools->firstItem() - 1 }}.</td>
                                <td>
                                    <a href="{{ route('admin.school.show', $school->id) }}">{{$school->name}}</a>
                                </td>
                                <td>{{$school->username}}</td>
                                <td>{{$school->code}}</td>
                                <td><pre class="text-center">{{$school->email}}</pre></td>
                                <td>
                                    <ul class="list-unstyled">
                                    @foreach ($school->categories as $category)
                                        <li>{{$category->name}}</li>
                                    @endforeach
                                    </ul>
                                </td>
                                @if($school->active)
                                    <td class="text-center text-success">
                                        @icon('check')
                                    </td>
                                @else
                                    <td class="text-center text-danger">
                                        @icon('times')
                                    </td>
                                @endif
                                <td>{{$school->user->name}}</td>
                                <td>
                                    @if(Auth::user()->roles->where('name', 'Administrator')->count() > 0)
                                        <a href="{{ route('admin.school.edit', $school) }}" class="btn btn-primary m-1">@icon('fas fa-edit') Επεξεργασία</a><br/>
                                    @endif
                                </td>
                                <td>
                                    @if(Auth::user()->roles->where('name', 'Administrator')->count() > 0)
                                        <form action="{{ route('admin.school.destroy', $school->id)}}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">@icon('fas fa-trash-alt') Διαγραφή</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10">Δεν βρέθηκαν σχολικές μονάδες</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table> --}}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
