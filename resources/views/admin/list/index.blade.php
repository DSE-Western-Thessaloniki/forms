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
                                    <div class="col-xl-6">
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
                                                    <div class="col-lg-8 d-flex flex-column justify-content-between">
                                                        <div>@icon('fas fa-hashtag') {{ substr_count($list->data, '"id"') }} στοιχεία</div>
                                                        <div class="flex-wrap fst-italic">
                                                            <small class="text-black-50">Δημιουργήθηκε από: {{$list->created_by()->first()->name}}</small>
                                                        </div>
                                                        @php
                                                            $updated_by = $list->updated_by()->first();
                                                        @endphp
                                                        @if($updated_by)
                                                        <div class="flex-wrap">
                                                            <small class="text-black-50">Τροποποιήθηκε από: {{$updated_by->name}}</small>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <a href="{{ route('admin.list.copy', $list) }}"
                                                            class="btn btn-success m-1 w-100">@icon('fas fa-copy')
                                                            Αντιγραφή</a><br />
                                                        @can('update', $list)
                                                        <a href="{{ route('admin.list.edit', $list) }}"
                                                            class="btn btn-primary m-1 w-100">@icon('fas fa-edit')
                                                            Επεξεργασία</a><br />
                                                        @endcan
                                                        @can('delete', $list)
                                                        <a class="btn btn-danger m-1 w-100"
                                                            href="{{ route('admin.list.confirmDelete', $list) }}">@icon('fas fa-trash-alt')
                                                            Διαγραφή</a>
                                                        @endcan
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
