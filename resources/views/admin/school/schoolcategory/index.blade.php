@extends('layouts.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Κατηγορίες</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="container">
                            <div class="btn-toolbar pb-2" role="toolbar">
                                <div class="btn-group me-2">
                                    <a class="btn btn-primary" href="{{ route('admin.school.schoolcategory.create') }}">
                                        @icon('fas fa-plus') Δημιουργία Κατηγορίας
                                    </a>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    Κατηγορίες
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        @forelse($categories as $category)
                                            <li class="list-group-item">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        {{ $loop->iteration }}. <a
                                                            href="{{ route('admin.school.schoolcategory.show', $category->id) }}">{{ $category->name }}</a>
                                                        @php
                                                            $active_schools = array_filter($category->schools->toArray(), function ($school) {
                                                                return $school['active'];
                                                            });
                                                        @endphp
                                                        <span class="badge bg-success">{{ count($active_schools) }}</span>
                                                        @if (count($active_schools) != count($category->schools))
                                                            <span
                                                                class="badge bg-danger">{{ count($category->schools) - count($active_schools) }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-8">
                                                        @foreach ($category->schools->sortBy('name') as $school)
                                                            @if ($school->active)
                                                                <label
                                                                    class="rounded bg-success m-1 p-1">{{ $school->name }}</label>
                                                            @else
                                                                <label
                                                                    class="rounded bg-danger m-1 p-1">{{ $school->name }}</label>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    <div class="col-md-2">
                                                        <a href="{{ route('admin.school.schoolcategory.edit', $category) }}"
                                                            class="btn btn-primary m-1">Επεξεργασία</a><br />
                                                        <form
                                                            action="{{ route('admin.school.schoolcategory.destroy', $category) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger m-1"
                                                                type="submit">Διαγραφή</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item">
                                                Δεν βρέθηκαν κατηγορίες
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
