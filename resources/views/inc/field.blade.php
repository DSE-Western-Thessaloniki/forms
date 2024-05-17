@if ($field->type == \App\Models\FormField::TYPE_TEXT) <!-- Πεδίο κειμένου -->
<input
    type="text"
    class="form-control"
    id="f{!!$field->id!!}"
    name="f{!!$field->id!!}"
    value="{{ $data }}"
    {!! $disabled ?? '' !!}
    {{ $field->required ? 'required' : '' }}
>
@endif
@if ($field->type == \App\Models\FormField::TYPE_TEXTAREA) <!-- Περιοχή κειμένου -->
<textarea
    class="form-control"
    id="f{!!$field->id!!}"
    name="f{!!$field->id!!}"
    rows="4"
    {!! $disabled ?? '' !!}
    {{ $field->required ? 'required' : '' }}
>{{ $data }}</textarea>
@endif
@if ($field->type == \App\Models\FormField::TYPE_RADIO_BUTTON) <!-- Επιλογή ενός από πολλά -->
@foreach (json_decode($field->listvalues) as $listvalues)
<div class="form-check">
    <input
        type="radio"
        class="form-check-input"
        name="f{!!$field->id!!}"
        id="f{!!$field->id!!}l{!!$listvalues->id!!}"
        value="{!!$listvalues->id!!}"
        {!! $listvalues->id == $data ? 'checked' : '' !!}
        {!! $disabled ?? '' !!}
    >
    <label class="form-check-label" for="f{!!$field->id!!}l{!!$listvalues->id!!}">
        {!!$listvalues->value!!}
    </label>
</div>
@endforeach
@endif
@if ($field->type == \App\Models\FormField::TYPE_CHECKBOX) <!-- Πολλαπλή επιλογή -->
@php
    $selected = json_decode($data);
@endphp
@foreach (json_decode($field->listvalues) as $listvalues)
<div class="form-check">
    <input
        type="checkbox"
        class="form-check-input"
        name="f{!!$field->id!!}[]"
        id="f{!!$field->id!!}l{!!$listvalues->id!!}"
        value="{!!$listvalues->id!!}"
        {!! $disabled ?? '' !!}
        {!! $selected && in_array($listvalues->id, $selected) ? 'checked' : '' !!}
    >
    <label class="form-check-label" for="f{!!$field->id!!}l{!!$listvalues->id!!}">
        {!!$listvalues->value!!}
    </label>
</div>
@endforeach
@endif
@if ($field->type == \App\Models\FormField::TYPE_SELECT) <!-- Λίστα επιλογών -->
<select class="form-control" id="f{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
    @foreach (json_decode($field->listvalues) as $listvalues)
        <option
            value="{!!$listvalues->id!!}"
            {!! $listvalues->id == $data ? 'selected' : '' !!}
        >
            {!!$listvalues->value!!}
        </option>
    @endforeach
</select>
@endif
@if ($field->type == \App\Models\FormField::TYPE_FILE) <!-- Αρχείο -->
@php
    $options = json_decode($field->options);
    $filetype_value = $options->filetype->value;

    if ($filetype_value != 999) {
        $accepted = \App\Models\AcceptedFiletype::find($filetype_value)->extension;
    } else {
        $accepted = $options->filetype->custom_value;
    }

    $route = route('report.download', [$form->id, $field->id, $record ?? 0]);
@endphp
<div class="row">
    @if ($data)
        <div class="mb-2">Ήδη ανεβασμένο αρχείο: <a href={{$route}}>{{ $data }}</a>. Αν θέλετε να το αλλάξετε επιλέξτε ένα νέο αρχείο από κάτω.</div>
    @endif
    <input
        type="file"
        class="form-control mb-2"
        id="f{!!$field->id!!}"
        name="f{!!$field->id!!}"
        {!! $disabled ?? '' !!}
        accept="{{ $accepted }}"
        {{ $field->required ? 'required' : '' }}
    >
    <div class="">Σημείωση: Μπορείτε να ανεβάσετε μόνο ένα αρχείο</div>
</div>
@endif
@if ($field->type == \App\Models\FormField::TYPE_DATE) <!-- Ημερομηνία -->
<input
    type="date"
    class="form-control"
    id="f{!!$field->id!!}"
    name="f{!!$field->id!!}"
    value="{{ $data }}"
    {!! $disabled ?? '' !!}
    {{ $field->required ? 'required' : '' }}
>
@endif
@if ($field->type == \App\Models\FormField::TYPE_NUMBER) <!-- Αριθμός -->
<input
    type="number"
    class="form-control"
    id="f{!!$field->id!!}"
    name="f{!!$field->id!!}"
    value="{{ $data == '' ? 0 : $data }}"
    {!! $disabled ?? '' !!}
    {{ $field->required ? 'required' : '' }}
>
@endif
@if ($field->type == \App\Models\FormField::TYPE_TELEPHONE) <!-- Τηλέφωνο -->
<input
    type="tel"
    class="form-control"
    id="f{!!$field->id!!}"
    name="f{!!$field->id!!}"
    pattern="[0-9]{10}"
    value="{{ $data }}"
    {!! $disabled ?? '' !!}
    {{ $field->required ? 'required' : '' }}
>
<small>Μορφή: 1234567890</small>
@endif
@if ($field->type == \App\Models\FormField::TYPE_EMAIL) <!-- E-mail -->
<input
    type="email"
    class="form-control"
    id="f{!!$field->id!!}"
    name="f{!!$field->id!!}"
    value="{{ $data }}"
    {!! $disabled ?? '' !!}
    {{ $field->required ? 'required' : '' }}
>
@endif
@if ($field->type == \App\Models\FormField::TYPE_URL) <!-- Url -->
<input
    type="url"
    class="form-control"
    id="f{!!$field->id!!}"
    name="f{!!$field->id!!}"
    value="{{ $data }}"
    {!! $disabled ?? '' !!}
    {{ $field->required ? 'required' : '' }}
>
@endif
