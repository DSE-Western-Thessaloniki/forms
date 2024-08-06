@extends('layouts.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Εκπαιδευτικοί εκτός Διεύθυνσης</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="container-fluid">
                            <div class="btn-toolbar pb-2 justify-content-end" role="toolbar">
                                <form class="form-horizontal" id="search" method="GET"
                                    action="{{ route('admin.other_teacher.index') }}">
                                    <div class="input-group" role="group">
                                        <input type="search" class="form-control" placeholder="Κριτήρια αναζήτησης..."
                                            name="other_teacher_filter" value="{{ $filter }}">
                                        <button type="submit" class="btn btn-primary ms-2"
                                            form="search">Αναζήτηση</button>
                                    </div>
                                </form>
                            </div>

                            <div class="row justify-content-md-center">
                                <p class="text-danger"><strong>Τα στοιχεία του παρακάτω πίνακα συμπληρώνονται και
                                        ενημερώνονται αυτόματα κατά τη
                                        σύνδεση
                                        των εκπαιδευτικών σύμφωνα με τα στοιχεία που είναι καταχωρημένα στο Πανελλήνιο
                                        Σχολικό Δίκτυο.</strong></p>
                                <table class="table table-responsive table-bordered text-center">
                                    <thead>
                                        <th>Α/Α</th>
                                        <th>ΑΜ/ΑΦΜ</th>
                                        <th>Όνοματεπώνυμο</th>
                                        <th>E-mail</th>
                                    </thead>
                                    <tbody>
                                        @forelse($other_teachers as $other_teacher)
                                            <tr>
                                                <td class="align-middle">
                                                    {{ $loop->iteration + $other_teachers->firstItem() - 1 }}.
                                                </td>
                                                <td class="align-middle">
                                                    {{ $other_teacher->employeenumber }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $other_teacher->name }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $other_teacher->email }}
                                                </td>
                                            </tr>
                                    </tbody>

                                @empty
                                    <tr>
                                        <td colspan="4">
                                            Δεν βρέθηκαν εκπαιδευτικοί εκτός Διεύθυνσης
                                        </td>
                                    </tr>
                                    @endforelse
                                </table>
                            </div>
                            <div class="row justify-content-md-center">
                                {{ $other_teachers->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
