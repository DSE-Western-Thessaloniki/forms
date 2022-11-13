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
                            <div class="btn-toolbar pb-2 justify-content-between" role="toolbar">
                                <div class="btn-group me-2">
                                    <a class="btn btn-primary" href="{{ route('admin.school.create') }}"
                                        test-data-id="btn-school-create">
                                        @icon('fas fa-plus') Δημιουργία σχολικής μονάδας
                                    </a>
                                    <a class="btn btn-success" href="{{ route('admin.school.schoolcategory.index') }}"
                                        test-data-id='btn-school-category-index'>
                                        @icon('fas fa-list') Διαχείριση κατηγοριών
                                    </a>
                                    <a class="btn btn-secondary" href="{{ route('admin.school.show_import') }}">
                                        @icon('fas fa-file-csv') Εισαγωγή δεδομένων σχολικών μονάδων
                                    </a>
                                </div>
                                <form class="form-horizontal" id="search" method="GET"
                                    action="{{ route('admin.school.index') }}">
                                    <div class="input-group" role="group">
                                        <input type="text" class="form-control" placeholder="Κριτήρια αναζήτησης..."
                                            name="filter" value="{{ $filter }}">
                                        <button type="submit" class="btn btn-primary ms-2"
                                            form="search">Αναζήτηση</button>
                                    </div>
                                </form>
                            </div>

                            <div class="row justify-content-md-center">
                                @forelse($schools as $school)
                                    <div class="col-xl-4">
                                        <div class="card my-2 shadow">
                                            @if ($school->active)
                                                <div class="card-header">
                                                @else
                                                    <div class="card-header bg-danger text-white">
                                            @endif
                                            <strong>{{ $loop->iteration + $schools->firstItem() - 1 }}.
                                                {{ $school->name }}</strong>
                                            @if (!$school->active)
                                                <em>(Ανενεργή)</em>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-auto d-flex flex-column flex-grow-1">
                                                    <div>@icon('fas fa-user') Όνομα χρήστη: {{ $school->username }}</div>
                                                    <div>@icon('fas fa-school') Κωδικός ΥΠΑΙΘ: {{ $school->code }}</div>
                                                    <div>@icon('fas fa-envelope') E-mail: {{ $school->email }}</div>
                                                    <div>@icon('fas fa-phone-alt') Τηλέφωνο: {{ $school->telephone }}</div>
                                                    <div class="flex-grow-1">
                                                        @icon('fas fa-tags') Κατηγορίες:
                                                        @foreach ($school->categories as $category)
                                                            <span class="rounded bg-warning p-1">@icon('fas fa-tag')
                                                                {{ $category->name }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="col-lg-auto">
                                                    @if (Auth::user()->roles->where('name', 'Administrator')->count() > 0)
                                                        <a href="{{ route('admin.school.edit', $school) }}"
                                                            class="btn btn-primary m-1">@icon('fas fa-edit')
                                                            Επεξεργασία</a><br />
                                                    @endif
                                                    @if (Auth::user()->roles->where('name', 'Administrator')->count() > 0)
                                                        <a class="btn btn-danger m-1"
                                                            href="{{ route('admin.school.confirmDelete', $school) }}">@icon('fas fa-trash-alt')
                                                            Διαγραφή</a>
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
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection
