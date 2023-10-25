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

                    <div class="container">
                        <div class="card my-2 shadow-sm">
                            <div class="card-header">
                                Επιβεβαίωση διαγραφής λίστας
                            </div>
                            <div class="card-body">
                                <p class="h3 text-danger">Είστε σίγουροι ότι θέλετε να διαγράψετε τη λίστα "{{ $list->name }}";</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.list.index') }}" class="btn btn-secondary">Άκυρο</a>
                                    @if(Auth::user()->roles->where('name', 'Administrator')->count() > 0 || Auth::user()->roles->where('name', 'Author')->count() > 0)
                                    <form action="{{ route('admin.list.destroy', $list->id)}}" method="post" class="float-right">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit" data-bs-toggle="tooltip" data-placement="top" title="Διαγραφή">@icon('fas fa-trash-alt') Διαγραφή</button>
                                    </form>
                                    @endif
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
