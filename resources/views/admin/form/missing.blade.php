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
                            <a href="{{route('admin.form.index')}}" class="btn btn-primary" role="button">@icon('fas fa-long-arrow-alt-left') Πίσω</a>
                            <a class="btn btn-danger" href="{{ route('admin.form.missing.csv', $form)}}">
                                @icon('fas fa-file-csv') Λήψη αρχείου csv
                            </a>
                            <a class="btn btn-success" href="{{ route('admin.form.missing.xlsx', $form)}}">
                                @icon('fas fa-file-excel') Λήψη αρχείου excel
                            </a>
                        </div>
                    </div>
                    <h1>{{$form->title}}</h1>
                    <h3>Σχολικές μονάδες που δεν απάντησαν:</h3>
                    <hr/>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Α/Α</th>
                                    <th>Σχολική Μονάδα</th>
                                    <th>Κωδικός</th>
                                    <th>Τηλέφωνο</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schools as $school)
                                    <tr>
                                        <td>
                                            {{ $loop->index + 1 }}
                                        </td>
                                        <td>
                                            {{ $school->name }}
                                        </td>
                                        <td>
                                            {{ $school->code }}
                                        </td>
                                        <td>
                                            {{ $school->telephone }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">
                                            Δεν υπάρχει καμία σχολική μονάδα που να μην απάντησε!!!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <hr/>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
