<div class="form-group row">
    <label for="{!!$field->title!!}" class="col-sm-3 col-form-label">
        {{ $field->title }}
    </label>
    <div class="col-sm-9">
        @php
            $data = "";
            $data_collection = $field->field_data()->where('school_id', session('school_id'))->first();
            if ($data_collection) {
                $data_array = $data_collection->toArray();
                try {
                    $data = $data_array['data'];
                }
                catch (\Exception $e) {
                    $data = '';
                }
            }
        @endphp

        @if ($field->type == 0) <!-- Πεδίο κειμένου -->
            <input
                type="text"
                class="form-control"
                id="f{!!$field->id!!}"
                name="f{!!$field->id!!}"
                value="{!! $data !!}"
                {!! $disabled ?? '' !!}
            >
        @endif
        @if ($field->type == 1) <!-- Περιοχή κειμένου -->
            <textarea
                class="form-control"
                id="f{!!$field->id!!}"
                name="f{!!$field->id!!}"
                rows="4"
                {!! $disabled ?? '' !!}
            >{{ $data }}</textarea>
        @endif
        @if ($field->type == 2) <!-- Επιλογή ενός από πολλά -->
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
        @if ($field->type == 3) <!-- Πολλαπλή επιλογή -->
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
                    {!! in_array($listvalues->id, $selected) ? 'checked' : '' !!}
                >
                <label class="form-check-label" for="f{!!$field->id!!}l{!!$listvalues->id!!}">
                    {!!$listvalues->value!!}
                </label>
            </div>
            @endforeach
        @endif
        @if ($field->type == 4) <!-- Λίστα επιλογών -->
            <select class="form-control" id="{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
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
        @if ($field->type == 5) <!-- Αρχείο -->
            <input type="file" class="form-control-file" id="f{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
        @endif
        @if ($field->type == 6) <!-- Ημερομηνία -->
            <input
                type="date"
                class="form-control"
                id="f{!!$field->id!!}"
                name="f{!!$field->id!!}"
                value="{!! $data !!}"
                {!! $disabled ?? '' !!}
            >
        @endif
        @if ($field->type == 7) <!-- Αριθμός -->
            <input
                type="number"
                class="form-control"
                id="f{!!$field->id!!}"
                name="f{!!$field->id!!}"
                value="{!! $data !!}"
                {!! $disabled ?? '' !!}
            >
        @endif
        @if ($field->type == 8) <!-- Τηλέφωνο -->
            <input
                type="tel"
                class="form-control"
                id="f{!!$field->id!!}"
                name="f{!!$field->id!!}"
                pattern="[0-9]{10}"
                value="{!! $data !!}"
                {!! $disabled ?? '' !!}
            >
            <small>Μορφή: 1234567890</small>
        @endif
        @if ($field->type == 9) <!-- E-mail -->
            <input
                type="email"
                class="form-control"
                id="f{!!$field->id!!}"
                name="f{!!$field->id!!}"
                value="{!! $data !!}"
                {!! $disabled ?? '' !!}
            >
        @endif
        @if ($field->type == 10) <!-- Url -->
            <input
                type="url"
                class="form-control"
                id="f{!!$field->id!!}"
                name="f{!!$field->id!!}"
                value="{!! $data !!}"
                {!! $disabled ?? '' !!}
            >
        @endif
    </div>
</div>
