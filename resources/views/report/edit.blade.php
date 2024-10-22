@extends('layouts.app')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @php
        if ($form->multiple) {
            $record = $record ?? 0;
        } else {
            $record = 0;
        }
        $save = true;

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
        $total_records++;

        // Form action
        if ($form->multiple) {
            $action = route('report.edit.record.update', [$form->id, $record, 'exit']);
        } else {
            $action = route('report.update', $form->id);
        }

        // Κανόνισε τα δεδομένα για τα πεδία που δέχονται αρχείο
        foreach ($form->form_fields as $field) {
            if ($field->type == \App\Models\FormField::TYPE_FILE) {
                $options = json_decode($field->options);
                $filetype_value = $options->filetype->value;

                if ($filetype_value != -1) {
                    $accepted = \App\Models\AcceptedFiletype::find($filetype_value)->extension;
                } else {
                    $accepted = $options->filetype->custom_value;
                }

                // Πρόσθεσε ένα επιπλέον πεδίο στο μοντέλο δυναμικά
                $field->accepted = $accepted;
            }
        }

        $errors = json_encode($errors->getBag('default'), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        // Τα σφάλματα περνάνε από json_decode αργότερα για να γίνουν αντικείμενο
        // οπότε για να μην σκάσει παρακάτω μετατρέπουμε τον κενό πίνακα σε
        // κενό αντικείμενο.
        if ($errors === '[]') {
            $errors = '{}';
        }
    @endphp
    <v-form action="{{ $action }}" :form="{{ $form }}" :record="{{ $record }}"
        :total_records="{{ $total_records }}" :form_data="{{ json_encode($data_dict) }}"
        acting_as="{{ $teacher?->surname }} {{ $teacher?->name }} {{ $other_teacher?->name }} {{ $school?->name }}"
        :save="{{ $save ? 'true' : 'false' }}" method="put" :old="{{ json_encode(old()) }}" errors="{{ $errors }}">
        <template #description>{!! Str::replace('<a ', '<a target="_blank" ', Str::of($form->notes)->markdown(['html_input' => 'strip'])) !!}</template>
        <template #csrf_token>@csrf</template>
    </v-form>
@endsection
