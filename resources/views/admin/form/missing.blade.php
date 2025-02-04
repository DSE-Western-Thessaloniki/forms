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
                            <div class="btn-group me-2">
                                <button onclick="history.back()" class="btn btn-primary" role="button">@icon('fas fa-long-arrow-alt-left')
                                    Πίσω</button>
                                <a class="btn btn-danger" href="{{ route('admin.form.missing.csv', $form) }}">
                                    @icon('fas fa-file-csv') Λήψη αρχείου csv
                                </a>
                                <a class="btn btn-success" href="{{ route('admin.form.missing.xlsx', $form) }}">
                                    @icon('fas fa-file-excel') Λήψη αρχείου excel
                                </a>
                            </div>
                        </div>
                        <h1>{{ $form->title }}</h1>
                        @if ($form->for_teachers)
                            <h3>Εκπαιδευτικοί που δεν απάντησαν:</h3>
                        @else
                            <h3>Σχολικές μονάδες που δεν απάντησαν:</h3>
                        @endif
                        <hr />
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Α/Α</th>
                                        @foreach ($missing_data[0] as $header)
                                            <th>{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $columns = count($missing_data[0]) + 1;
                                        // Αφαίρεσε την γραμμή με τις επικεφαλίδες
                                        unset($missing_data[0]);
                                    @endphp
                                    @forelse ($missing_data as $row)
                                        <tr>
                                            <td>
                                                {{ $loop->index + 1 }}
                                            </td>
                                            @foreach ($row as $data)
                                                <td>
                                                    {{ $data }}
                                                </td>
                                            @endforeach
                                        </tr>

                                    @empty
                                        @if ($form->for_teachers)
                                            <tr>
                                                <td class="text-center" colspan="{{ $columns }}">
                                                    Δεν υπάρχει κανένας εκπαιδευτικός που να μην απάντησε!!!
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td class="text-center" colspan="{{ $columns }}">
                                                    Δεν υπάρχει καμία σχολική μονάδα που να μην απάντησε!!!
                                                </td>
                                            </tr>
                                        @endif
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <hr />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
