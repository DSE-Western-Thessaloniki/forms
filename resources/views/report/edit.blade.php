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
                $table = array();
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
                }
                @endphp

                @if($form->multiple) {{-- Αν επιτρέπονται πολλαπλές εγγραφές --}}
                    <div class="d-none" id="table_template">
                        @include('inc.formfieldstabletemplate')
                    </div>
                    <editabledatatable
                        :columns="{{ $form->form_fields->toJson() }}"
                        :data="{{ json_encode($table) }}"
                    >
                    </editabledatatable>
                @else {{-- Αν επιτρέπεται μόνο μια εγγραφή --}}
                    @foreach ($form->form_fields as $field)
                        @include('inc.formfields')
                    @endforeach
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
                <button class='btn btn-primary' type='submit'>Αποθήκευση</a>
            </div>
        </div>
        @csrf

    </form>

@endsection
