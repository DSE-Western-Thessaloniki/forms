
@php
foreach($form->form_fields as $field) {
    $data = "";
    $data_collection = $field->field_data()->where('school_id', session('school_id'))->get();
    $rows = $data_collection->count() ?? 0;
    if ($data_collection) {
        $data_array = $data_collection->toArray();
        for ($i=0; $i < $rows; $i++) {
            $table[$field->name][$i] = $data_array[$i]['data'] ?? '';
        }
    }
}
@endphp

<div id="trow">
    @foreach ($form->form_fields as $field)
        @php
            $data = '';
        @endphp
        <div id="tdata">@include('inc.field')</div>
    @endforeach
</div>
