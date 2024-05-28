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
        // Απενεργοποίησε όλες τα πεδία της φόρμας για να μην μπορούν
        // να αλλάξουν τις τιμές
        $disabled = 'disabled';
        if ($form->multiple) {
            $record = $record ?? 0;
        } else {
            $record = 0;
        }
    @endphp

    <br />
    <a href="{{ route('report.index') }}" class="btn btn-secondary" role="button">Πίσω</a>
    <form action="javascript:void(0);">
        <h1>{{ $form->title }}</h1>
        <h3>{!! Str::of($form->notes)->markdown(['html_input' => 'strip']) !!}</h3>
        <hr />
        <div class="card">
            <div class="card-header">
                Συμπληρωμένη φόρμα - <span class="h5 fw-bold">{{ $school?->name }} {{ $teacher?->surname }} {{ $teacher?->name }} {{ $other_teacher?->name }}</span>
            </div>
            <div class="card-body">
                @php
                    if ($school !== null) {
                        $total_records = $form
                            ->data()
                            ->where('school_id', $school->id)
                            ->max('record');
                    } else if ($teacher !== null) {
                        $total_records = $form
                            ->data()
                            ->where('teacher_id', $teacher->id)
                            ->max('record');
                    } else {
                        $total_records = $form
                            ->data()
                            ->where('other_teacher_id', $other_teacher->id)
                            ->max('record');
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
                                        formaction="{{ route('report.show.record', [$form->id, $record > 0 ? $record - 1 : 0]) }}"
                                        formmethod="get" {{ $record > 0 ? "tabindex='-1' aria-disabled='true'" : '' }}>
                                        @icon('fas fa-chevron-left')
                                    </button>
                                </li>
                                @for ($i = 0; $i < $total_records + 1; $i++)
                                    <li class="page-item {{ $i == $record ? 'active' : '' }}">
                                        @if ($i == $record)
                                            <button type="button" class="page-link">{{ $i + 1 }}</a>
                                            @else
                                                <button class="page-link" type="submit"
                                                    formaction="{{ route('report.show.record', [$form->id, $i]) }}"
                                                    formmethod="get">
                                                    {{ $i + 1 }}
                                                </button>
                                        @endif
                                    </li>
                                @endfor
                                <li class="page-item {{ $record < $total_records ? '' : 'disabled' }}">
                                    <button class="page-link" type="submit"
                                        formaction="{{ route('report.show.record', [$form->id, $record < $total_records ? $record + 1 : $total_records]) }}"
                                        formmethod="get"
                                        {{ $record >= $total_records ? "tabindex='-1' aria-disabled='true'" : '' }}>
                                        @icon('fas fa-chevron-right')
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
        <a href="{{ route('report.edit', $form->id) }}" class="btn btn-primary">Επεξεργασία</a>
    </form>
@endsection
