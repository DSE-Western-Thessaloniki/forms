@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Φόρμες</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="btn-toolbar pb-2" role="toolbar">
                        <div class="btn-group mr-2">
                            <a class="btn btn-primary" href="{{ route('admin.form.index') }}">
                                @icon('fas fa-long-arrow-alt-left') Επιστροφή
                            </a>
                            <a class="btn btn-success" href="{{ route('admin.form.data.csv', $form)}}">
                                @icon('fas fa-file-csv') Λήψη αρχείου csv
                            </a>
                        </div>
                    </div>
                    <vdatatable-component
                        :columns="{{ json_encode($dataTableColumns) }}"
                        :data="{{ json_encode($dataTable) }}"
                        :schools="{{ json_encode($schools) }}"
                    >
                    </vdatatable-component>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
