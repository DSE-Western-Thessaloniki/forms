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
        if ($form->multiple) {
            $record = $record ?? 0;
        } else {
            $record = 0;
        }
        $save = true;
    @endphp

    @if ($form->multiple)
        <form action={{ route('report.edit.record.update', [$form->id, $record, 'exit']) }} method='post'>
        @else
            <form action={{ route('report.update', $form->id) }} method='post'>
    @endif

    <h1>{{ $form->title }}</h1>
    <h3>{!! nl2br(strip_tags($form->notes)) !!}</h3>
    <hr />
    <div class="card">
        <div class="card-header">
            Συμπλήρωση φόρμας ως <span class="h5 fw-bold">{{ $teacher?->surname }} {{ $teacher?->name }} {{ $other_teacher?->name }} {{ $school?->name }}</span>
        </div>
        <div class="card-body">
            <div class="mb-4">To <span class="text-danger">*</span> σηματοδοτεί υποχρεωτικά πεδία.</div>
            @php
                if ($school !== null) {
                    $total_records = DB::table('form_fields')
                        ->join('form_field_data', 'form_fields.id', '=', 'form_field_data.form_field_id')
                        ->where('form_id', $form->id)
                        ->where('school_id', $school->id)
                        ->max('record');
                } else {
                    if ($teacher !== null) {
                        $total_records = DB::table('form_fields')
                            ->join('form_field_data', 'form_fields.id', '=', 'form_field_data.form_field_id')
                            ->where('form_id', $form->id)
                            ->where('teacher_id', $teacher->id)
                            ->max('record');
                    } else {
                        $total_records = DB::table('form_fields')
                            ->join('form_field_data', 'form_fields.id', '=', 'form_field_data.form_field_id')
                            ->where('form_id', $form->id)
                            ->where('other_teacher_id', $other_teacher->id)
                            ->max('record');
                    }
                }
                $total_records = $total_records ?? 0;
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

                @if ($form->multiple)
                    {{-- Αν επιτρέπονται πολλαπλές εγγραφές --}}

                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item {{ $record > 0 ? '' : 'disabled' }}">
                                <button class="page-link" type="submit"
                                    formaction="{{ route('report.edit.record.update', [$form->id, $record, $record > 0 ? $record - 1 : 0]) }}"
                                    formmethod="post" {{ $record > 0 ? "tabindex='-1' aria-disabled='true'" : '' }}>
                                    @icon('fas fa-chevron-left')
                                </button>
                            </li>
                            @for ($i = 0; $i < $total_records + 1; $i++)
                                <li class="page-item {{ $i == $record ? 'active' : '' }}">
                                    @if ($i == $record)
                                        <button type="button" class="page-link">{{ $i + 1 }}</a>
                                        @else
                                            <button class="page-link" type="submit"
                                                formaction="{{ route('report.edit.record.update', [$form->id, $record, $i]) }}"
                                                formmethod="post">
                                                {{ $i + 1 }}
                                            </button>
                                    @endif
                                </li>
                            @endfor
                            <li class="page-item {{ $record < $total_records ? '' : 'disabled' }}">
                                <button class="page-link" type="submit"
                                    formaction="{{ route('report.edit.record.update', [$form->id, $record, $record < $total_records ? $record + 1 : $total_records]) }}"
                                    formmethod="post"
                                    {{ $record >= $total_records ? "tabindex='-1' aria-disabled='true'" : '' }}>
                                    @icon('fas fa-chevron-right')
                                </button>
                            </li>
                            <li class="page-item">
                                <button class="page-link" type="submit"
                                    formaction="{{ route('report.edit.record.update', [$form->id, $record, 'new']) }}">
                                    @icon('fas fa-asterisk') Νέα εγγραφή
                                </button>
                            </li>
                        </ul>
                    </nav>
                    <hr />
                @endif
            @endif

        </div>
    </div>
    <hr />
    <div class="form-group row mb-3">
        <div class="col-2">
            <a class="btn btn-danger" href="{{ route('report.index') }}">Ακύρωση</a>
        </div>
        <div class="col d-flex justify-content-end">
            @method('PUT')
            @if ($save)
                <button class='btn btn-primary' type='submit'>Αποθήκευση</a>
            @endif
        </div>
    </div>
    @csrf

    </form>

@endsection
