@php
$data = "";
if (isset($school)) {
    $data_collection = $field->field_data()
        ->where('school_id', $school->id)
        ->where('record', $record ?? 0)
        ->first();

    if ($data_collection) {
        $data_array = $data_collection->toArray();
        try {
            $data = $data_array['data'];
        }
        catch (\Exception $e) {
            $data = '';
        }
    }
}
@endphp

<div class="form-group row">
    <label for="{!!$field->title!!}" class="col-sm-3 col-form-label">
        {{ $field->title }}
    </label>
    <div class="col-sm-9">
        @include('inc.field')
    </div>
</div>
