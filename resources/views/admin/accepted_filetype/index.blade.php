@extends('layouts.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Τύποι αρχείων που γίνονται δεκτοί στις φόρμες</div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="container">
                            <a class="btn btn-primary mb-3" href="{{ route('admin.accepted_filetype.create') }}">@icon('fas fa-plus') Προσθήκη νέας αποδεκτής επέκτασης</a>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <th>Περιγραφή</th>
                                    <th>Επεκτάσεις</th>
                                    <th>Ενέργειες</th>
                                </thead>
                                <tbody>
                                    @foreach ($accepted_filetypes as $accepted_filetype)
                                        <tr>
                                            <td>{{ $accepted_filetype->description }}</td>
                                            <td>{{ $accepted_filetype->extension }}</td>
                                            <td class="d-flex gap-3">
                                                <a class="btn btn-primary" href="{{ route('admin.accepted_filetype.edit', $accepted_filetype->id) }}">@icon('fas fa-pencil') Επεξεργασία</a>
                                                <form action="{{ route('admin.accepted_filetype.destroy', $accepted_filetype->id)}}" method="post" class="float-right">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger" type="submit">@icon('fas fa-trash-alt') Διαγραφή</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
