@extends('layouts.app')

@section('content')
    @if (session('status'))
    <div class="alert alert-success" role="alert">
        {{ session('status') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    </div><br />
    @endif

    <form action={{ route('report.update', $form->id) }} method='post'>

        <h1>{{$form->title}}</h1>
        <h3>{{$form->notes}}</h3>
        <hr/>
        <div class="card">
            <div class="card-header">
                {{$form->title}}
            </div>
            <div class="card-body">
                @php
                    $form_count = 0;
                @endphp
                @foreach ($form->form_fields as $field)
                    @include('inc.formfields')
                @endforeach
            </div>
        </div>
        <hr/>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                @php
                    $total_records = 0;
                    $records_exist = false;
                    foreach($form->form_fields as $field) {
                        if ($field->field_data->count()) {
                            $records_exist = true;
                        }
                        $max_record_count = $field->field_data->max('record');
                        if ($total_records < $max_record_count) {
                            $total_records = $max_record_count;
                        }
                    }

                    $record = $record ?? 0;
                @endphp
                <li class="page-item {{ $record > 0 ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ route('report.edit.record', [$form->id, $record > 0 ? ($record - 1) : 0]) }}" {{ $record > 0 ? "tabindex='-1' aria-disabled='true'" : '' }}>@icon('fas fa-chevron-left')</a>
                </li>
                @for($i = 0; $i < ($total_records + 1); $i++)
                <li class="page-item {{ $i == $record ? 'active' : '' }}" >
                    @if($i == $record)
                        <a class="page-link" href="#">{{ $i + 1 }}</a>
                    @else
                        <a class="page-link" href="{{ route('report.edit.record', [$form->id, $i]) }}">{{ $i + 1 }}</a>
                    @endif
                </li>
                @endfor
                <li class="page-item">
                    <a class="page-link" href="#">@icon('fas fa-chevron-right')</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="{{ route('report.edit.record', [$form->id, 1]) }}">@icon('fas fa-asterisk') Νέα εγγραφή</a>
                </li>
            </ul>
        </nav>
        <hr/>
        <input type="text" class="form-control" value="{{ $record }}"/>
        <hr/>
        <div class="form-group row">
            <div class="col-2">
                <a class="btn btn-danger" href="{{ route('report.index') }}">Ακύρωση</a>
            </div>
            <div class="col-10 d-flex justify-content-end">
                @method('PUT')
                <button class='btn btn-primary' type='submit'>Αποθήκευση</a>
            </div>
        </div>
        @csrf

    </form>

@endsection
