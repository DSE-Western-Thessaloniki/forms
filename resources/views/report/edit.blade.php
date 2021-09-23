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

    @php
        $record = $record ?? 0;
        $save = true;
    @endphp

    <form action={{ route('report.edit.record.update', [$form->id, $record, 'exit']) }} method='post'>

        <h1>{{$form->title}}</h1>
        <h3>{{$form->notes}}</h3>
        <hr/>
        <div class="card">
            <div class="card-header">
                {{$form->title}}
            </div>
            <div class="card-body">
                @php
                /*$table = array();
                foreach($form->form_fields as $field) {
                    $data = "";
                    $data_collection = $field->field_data()->where('school_id', session('school_id'))->get();
                    $rows = $data_collection->count() ?? 0;
                    if ($data_collection) {
                        $data_array = $data_collection->toArray();
                        for ($i=0; $i < $rows; $i++) {
                            $table[$field->title][$i] = $data_array[$i]['data'] ?? '';
                        }
                    }
                }*/
                @endphp

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
                @endphp

                @if ($record > $total_records)
                    Δεν υπάρχει η εγγραφή
                    @php
                        $save = false;
                    @endphp
                @else
                    @foreach ($form->form_fields as $field)
                    @include('inc.formfields')
                    @endforeach

                    @if($form->multiple) {{-- Αν επιτρέπονται πολλαπλές εγγραφές --}}

                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item {{ $record > 0 ? '' : 'disabled' }}">
                                    <button
                                        class="page-link"
                                        type="submit"
                                        formaction="{{ route('report.edit.record.update', [$form->id, $record, $record > 0 ? ($record - 1) : 0]) }}"
                                        formmethod="post"
                                        {{ $record > 0 ? "tabindex='-1' aria-disabled='true'" : '' }}
                                    >
                                        @icon('fas fa-chevron-left')
                                    </button>
                                </li>
                                @for($i = 0; $i < ($total_records + 1); $i++)
                                <li class="page-item {{ $i == $record ? 'active' : '' }}" >
                                    @if($i == $record)
                                        <button type="button" class="page-link">{{ $i + 1 }}</a>
                                    @else
                                        <button
                                            class="page-link"
                                            type="submit"
                                            formaction="{{ route('report.edit.record.update', [$form->id, $record, $i]) }}"
                                            formmethod="post"
                                        >
                                            {{ $i + 1 }}
                                        </button>
                                    @endif
                                </li>
                                @endfor
                                <li class="page-item {{ $record < $total_records ? '' : 'disabled' }}">
                                    <button
                                        class="page-link"
                                        type="submit"
                                        formaction="{{ route('report.edit.record.update', [$form->id, $record, $record < $total_records ? $record + 1 : $total_records]) }}"
                                        formmethod="post"
                                        {{ $record >= $total_records ? "tabindex='-1' aria-disabled='true'" : '' }}
                                    >
                                        @icon('fas fa-chevron-right')
                                </button>
                                </li>
                                <li class="page-item">
                                    <button
                                        class="page-link"
                                        type="submit"
                                        formaction="{{ route('report.edit.record.update', [$form->id, $record, 'new']) }}"
                                    >
                                        @icon('fas fa-asterisk') Νέα εγγραφή
                                    </button>
                                </li>
                            </ul>
                        </nav>
                        <hr/>
                        <input type="text" class="form-control" value="{{ ($record + 1).' / '.($total_records + 1) }}" placeholder=""/>
                    @endif
                @endif

            </div>
        </div>
        <hr/>
        <hr/>
        <hr/>
        <div class="form-group row">
            <div class="col-2">
                <a class="btn btn-danger" href="{{ route('report.index') }}">Ακύρωση</a>
            </div>
            <div class="col-10 d-flex justify-content-end">
                @method('PUT')
                @if($save)
                    <button class='btn btn-primary' type='submit'>Αποθήκευση</a>
                @endif
            </div>
        </div>
        @csrf

    </form>

@endsection
