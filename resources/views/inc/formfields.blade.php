@php
$data = $data_dict[$field->id] ?? '';
@endphp

<div class="form-group row mb-3">
    <label for="f{{$field->id}}" class="col-md-3 col-form-label">
        {{ $field->title }} {!! $field->required ? '<span class="text-danger">*</span>' : '' !!}
    </label>
    @if($field->type == \App\Models\FormField::TYPE_NUMBER)
    <div class="col-md-3">
    @else
    <div class="col-md-9">
    @endif
        @include('inc.field')
    </div>
</div>
