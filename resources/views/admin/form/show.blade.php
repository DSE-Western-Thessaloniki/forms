@extends('layouts.admin.app')

@section('content')
    <div class="btn-group">
        <a href="{{route('admin.form.index')}}" class="btn btn-secondary" role="button">@icon('fas fa-long-arrow-alt-left') Πίσω</a>
        <a href="{{ route('admin.form.edit', $form->id) }}" class="btn btn-primary">@icon('fas fa-edit') Επεξεργασία</a>
        <button class="btn btn-danger" type="submit" form="delete">@icon('fas fa-trash-alt') Διαγραφή</button>
    </div>
    <h1>{{$form->title}}</h1>
    <h3>{!! Str::replace('<a ', '<a target="_blank" ', Str::of($form->notes)->markdown(['html_input' => 'strip'])) !!}</h3>
    <hr/>
    <div class="card">
        <div class="card-header">
            Προεπισκόπιση
        </div>
        <div class="card-body">
            @foreach ($form->form_fields as $field)
                @if ($field->type === \App\Models\FormField::TYPE_FILE)
                    @php
                        $options = json_decode($field->options);
                        $filetype_value = $options->filetype->value;
                        if ($filetype_value != -1) {
                            $accepted = \App\Models\AcceptedFiletype::find($filetype_value)->extension;
                        } else {
                            $accepted = $options->filetype->custom_value;
                        }
                        $route = route('report.download', [$form->id, $field->id, $record ?? 0]);
                    @endphp
                @endif
                <field-group :field="{{ $field }}" data="" :disabled="false" error="{{ $errors->first("f{$field->id}") }}"></field-group>
            @endforeach
        </div>
    </div>
    <small>Δημιουργήθηκε στις {{$form->created_at}}</small>
    <hr/>

    <!-- The following lines are needed to be able to delete a form -->
    <form action="{{ route('admin.form.destroy', $form->id)}}" id="delete" method="post" class="float-right">
        @csrf
        @method('DELETE')
    </form>
@endsection
