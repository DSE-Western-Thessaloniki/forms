@extends('layouts.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Λίστες</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="container-fluid">
                            <div class="btn-toolbar pb-2 justify-content-between" role="toolbar">
                                <div class="btn-group me-2">
                                    <a class="btn btn-primary" href="{{ route('admin.list.create') }}"
                                        test-data-id="btn-school-create">
                                        @icon('fas fa-plus') Δημιουργία λίστας
                                    </a>
                                    <a class="btn btn-secondary" href="{{ route('admin.list.show_import') }}">
                                        @icon('fas fa-file-csv') Εισαγωγή δεδομένων λίστας
                                    </a>
                                </div>
                                <form class="form-horizontal" id="search" method="GET"
                                    action="{{ route('admin.list.index') }}">
                                    <div class="input-group" role="group">
                                        <input type="text" class="form-control" placeholder="Κριτήρια αναζήτησης..."
                                            name="filter" value="{{ $filter }}">
                                        <button type="submit" class="btn btn-primary ms-2"
                                            form="search">Αναζήτηση</button>
                                    </div>
                                </form>
                            </div>

                            <div class="row justify-content-md-center">
                                @forelse($lists as $list)
                                    <div class="col-xl-4">
                                        <div class="card my-2 shadow">
                                            <div class="card-header">
                                                <strong>{{ $loop->iteration + $lists->firstItem() - 1 }}.
                                                    {{ $list->name }}</strong>
                                                @if (!$list->active)
                                                    <em>(Ανενεργή)</em>
                                                @endif
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-auto d-flex flex-column flex-grow-1">
                                                        <div>@icon('fas fa-hashtag') {{ substr_count($list->data, '"id"') }} στοιχεία</div>
                                                    </div>
                                                    <div class="col-lg-auto">
                                                        <a href="{{ route('admin.list.edit', $list) }}"
                                                            class="btn btn-primary m-1">@icon('fas fa-edit')
                                                            Επεξεργασία</a><br />
                                                        <a class="btn btn-danger m-1"
                                                            href="{{ route('admin.list.confirmDelete', $list) }}">@icon('fas fa-trash-alt')
                                                            Διαγραφή</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                Δεν βρέθηκαν λίστες
                                @endforelse
                            </div>
                        </div>
                        <div class="row justify-content-md-center">
                            {{ $lists->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection
