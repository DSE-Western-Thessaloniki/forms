<div class="form-group row">
    <label for="{!!$field->title!!}" class="col-sm-3 col-form-label">
        {{ $field->title }}
    </label>
    <div class="col-sm-9">
        @if ($field->type == 0)
            <input type="text" class="form-control" id="f{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
        @endif
        @if ($field->type == 1)
            <textarea class="form-control" id="f{!!$field->id!!}" name="f{!!$field->id!!}" rows="4" {!! $disabled ?? '' !!}>
            </textarea>
        @endif
        @if ($field->type == 2)
            @foreach (json_decode($field->listvalues) as $listvalues)
            <div class="form-check">
                <input type="radio" class="form-check-input" name="f{!!$field->id!!}" id="f{!!$field->id!!}l{!!$listvalues->id!!}" value="{!!$listvalues->id!!}" {!! $disabled ?? '' !!}>
                <label class="form-check-label" for="f{!!$field->id!!}l{!!$listvalues->id!!}">
                    {!!$listvalues->value!!}
                </label>
            </div>
            @endforeach
        @endif
        @if ($field->type == 3)
            @foreach (json_decode($field->listvalues) as $listvalues)
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="f{!!$field->id!!}" id="f{!!$field->id!!}l{!!$listvalues->id!!}" value="{!!$listvalues->id!!}" {!! $disabled ?? '' !!}>
                <label class="form-check-label" for="f{!!$field->id!!}l{!!$listvalues->id!!}">
                    {!!$listvalues->value!!}
                </label>
            </div>
            @endforeach
        @endif
        @if ($field->type == 4)
            <select class="form-control" id="{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
                @foreach (json_decode($field->listvalues) as $listvalues)
                    <option value="{!!$listvalues->id!!}">
                        {!!$listvalues->value!!}
                    </option>
                @endforeach
            </select>
        @endif
        @if ($field->type == 5)
            <input type="file" class="form-control-file" id="f{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
        @endif
        @if ($field->type == 6)
            <input type="date" class="form-control" id="f{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
        @endif
        @if ($field->type == 7)
            <input type="number" class="form-control" id="f{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
        @endif
        @if ($field->type == 8)
            <input type="tel" class="form-control" id="f{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
        @endif
        @if ($field->type == 9)
            <input type="email" class="form-control" id="f{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
        @endif
        @if ($field->type == 10)
            <input type="url" class="form-control" id="f{!!$field->id!!}" name="f{!!$field->id!!}" {!! $disabled ?? '' !!}>
        @endif
    </div>
</div>
