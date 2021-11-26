@php
$data = $data_dict[$field->id] ?? '';
@endphp

<div class="form-group row">
    <label for="{!!$field->title!!}" class="col-sm-3 col-form-label">
        {{ $field->title }}
    </label>
    <div class="col-sm-9">
        @include('inc.field')
    </div>
</div>
