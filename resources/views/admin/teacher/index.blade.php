@extends('layouts.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Εκπαιδευτικοί</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="container-fluid">
                            <div class="btn-toolbar pb-2 justify-content-between" role="toolbar">
                                <div class="btn-group me-2">
                                    <a class="btn btn-primary" href="{{ route('admin.teacher.create') }}"
                                        test-data-id="btn-teacher-create">
                                        @icon('fas fa-plus') Προσθήκη εκπαιδευτικού
                                    </a>
                                    <a class="btn btn-secondary" href="{{ route('admin.teacher.show_import') }}">
                                        @icon('fas fa-file-csv') Εισαγωγή δεδομένων εκπαιδευτικών
                                    </a>
                                </div>
                                <form class="form-horizontal" id="search" method="GET"
                                    action="{{ route('admin.teacher.index') }}">
                                    <div class="input-group" role="group">
                                        <input type="search" class="form-control" placeholder="Κριτήρια αναζήτησης..."
                                            name="teacher_filter" value="{{ $filter }}">
                                        <button type="submit" class="btn btn-primary ms-2"
                                            form="search">Αναζήτηση</button>
                                    </div>
                                </form>
                            </div>

                            <div class="row justify-content-md-center">
                                <table class="table table-responsive table-bordered text-center">
                                    <thead>
                                        <th>Α/Α</th>
                                        <th>Όνοματεπώνυμο</th>
                                        <th>ΑΜ</th>
                                        <th>ΑΦΜ</th>
                                        <th>Ενέργειες</th>
                                    </thead>
                                    <tbody>
                                        @forelse($teachers as $teacher)
                                            @if ($teacher->active)
                                                <tr class="">
                                                @else
                                                <tr class="table-danger">
                                            @endif
                                            <td class="align-middle">
                                                {{ $loop->iteration + $teachers->firstItem() - 1 }}.
                                            </td>
                                            <td class="align-middle">
                                                {{ $teacher->surname . ' ' . $teacher->name }}
                                                @if (!$teacher->active)
                                                    <em>(Ανενεργός)</em>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                {{ $teacher->am }}
                                            </td>
                                            <td class="align-middle">
                                                {{ $teacher->afm }}
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex">
                                                    @if (Auth::user()->roles->where('name', 'Administrator')->count() > 0)
                                                        <a href="{{ route('admin.teacher.edit', $teacher) }}"
                                                            class="btn btn-primary m-1">@icon('fas fa-edit')
                                                            Επεξεργασία</a><br />
                                                    @endif
                                                    @if (Auth::user()->roles->where('name', 'Administrator')->count() > 0)
                                                        <a class="btn btn-danger m-1"
                                                            href="{{ route('admin.teacher.confirmDelete', $teacher) }}">@icon('fas fa-trash-alt')
                                                            Διαγραφή</a>
                                                    @endif
                                                </div>
                                            </td>
                                            </tr>
                                    </tbody>

                                @empty
                                    Δεν βρέθηκαν σχολικές μονάδες
                                    @endforelse
                                </table>
                            </div>
                            <div class="row justify-content-md-center">
                                {{ $teachers->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
