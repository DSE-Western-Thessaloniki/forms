@php
$data = $data_dict[$field->id] ?? '';
@endphp

<div class="form-group row">
    <label for="{!!$field->title!!}" class="col-md-3 col-form-label">
        {{ $field->title }}
    </label>
    @if($field->type == 7)
    <div class="col-md-3">
    @else
    <div class="col-md-9">
    @endif
        @include('inc.field')
    </div>
</div>
