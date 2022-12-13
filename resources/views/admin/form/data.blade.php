@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
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
                        <div class="btn-group me-2">
                            <a class="btn btn-primary" href="{{ route('admin.form.index') }}">
                                @icon('fas fa-long-arrow-alt-left') Επιστροφή
                            </a>
                            <a class="btn btn-danger" href="{{ route('admin.form.data.csv', $form)}}">
                                @icon('fas fa-file-csv') Λήψη αρχείου csv
                            </a>
                            <a class="btn btn-success" href="{{ route('admin.form.data.xlsx', $form)}}">
                                @icon('fas fa-file-excel') Λήψη αρχείου excel
                            </a>
                        </div>
                    </div>
                    <vdatatable-component
                        :columns="{{ json_encode($dataTableColumns) }}"
                        :data="{{ json_encode($dataTable) }}"
                        :schools="{{ json_encode($schools) }}"
                        :teachers="{{ json_encode($teachers) }}"
                        :other_teachers="{{ json_encode($other_teachers) }}"
                        :for_teachers="{{ $form->for_teachers }}"
                        :for_all_teachers="{{ $form->for_all_teachers }}"
                    >
                    </vdatatable-component>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
