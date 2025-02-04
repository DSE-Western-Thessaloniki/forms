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
                                <button type="button" class="btn btn-primary" onclick="history.back()">
                                    @icon('fas fa-long-arrow-alt-left') Επιστροφή
                                </button>
                                <a class="btn btn-danger" href="{{ route('admin.form.data.csv', $form) }}">
                                    @icon('fas fa-file-csv') Λήψη αρχείου csv
                                </a>
                                <a class="btn btn-success" href="{{ route('admin.form.data.xlsx', $form) }}">
                                    @icon('fas fa-file-excel') Λήψη αρχείου excel
                                </a>

                                @php
                                    $hasFieldWithFiles =
                                        $form->form_fields->where('type', \App\Models\FormField::TYPE_FILE) !== null;
                                @endphp

                                @if ($hasFieldWithFiles)
                                    <a class="btn btn-primary" href="{{ route('admin.report.downloadAllFiles', $form) }}">
                                        @icon('fas fa-file-zipper') Λήψη αρχείων πεδίων
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="table-responsive d-flex max-600">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="fixed-header">
                                    <tr>
                                        @foreach ($dataTableColumns as $column)
                                            <th>
                                                {{ $column }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dataTable as $row)
                                        <tr>
                                            @foreach ($row as $item)
                                                <td>
                                                    @if (!is_array($item))
                                                        {{ $item }}
                                                    @else
                                                        @if (isset($item['file']) && $item['file'] === true)
                                                            <a href="{{ url($item['link']) }}">{{ $item['value'] }}</a>
                                                        @endif
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($links)
                            {!! $links !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
