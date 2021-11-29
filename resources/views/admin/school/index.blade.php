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

                    <div class="btn-toolbar pb-2" role="toolbar">
                        <div class="btn-group mr-2">
                            <a class="btn btn-primary" href="{{ route('admin.school.create')}}">
                            @icon('fas fa-plus') Δημιουργία σχολικής μονάδας
                            </a>
                            <a class="btn btn-success" href="{{ route('admin.school.schoolcategory.index')}}">
                                @icon('fas fa-list') Διαχείριση κατηγοριών
                                </a>
                            </div>
                    </div>

                    <div class="table-responsive">
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
                    </table>
                </div>
                {{ $schools->links() }}
            </div>
        </div>
    </div>
</div>
</div>
@endsection
