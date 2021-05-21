@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Κατηγορίες</div>

                @if(Auth::user()->isAdministrator())
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="btn-toolbar pb-2" role="toolbar">
                        <div class="btn-group mr-2">
                            <a class="btn btn-primary" href="{{ route('admin.school.schoolcategory.create')}}">
                            @icon('fas fa-plus') Δημιουργία Κατηγορίας
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Όνομα Κατηγορίας</th>
                                    <th>Αριθμός Σχολείων<br/>(αυτόματη καταμέτρηση)</th>
                                    <th>Id</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td>{{$loop->iteration}}.</td>
                                    <td><a href="{{route('admin.school.schoolcategory.show', $category->id)}}">{{$category->name}}</a></td>
                                    <td>{{ count($category->schools) }}</td>
                                    <td>{{$category->id}}</td>
                                    <td>
                                        <a href="{{ route('admin.school.schoolcategory.edit', $category) }}" class="btn btn-primary m-1">Επεξεργασία</a><br/>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.school.schoolcategory.destroy', $category) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">Διαγραφή</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8">Δεν βρέθηκαν κατηγορίες</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                    Δεν επιτρέπεται η πρόσβαση
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
