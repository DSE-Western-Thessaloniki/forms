<?php

namespace App\Http\Requests;

use App\Models\AcceptedFiletype;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;

class UpdateReportRequest extends FormRequest
{
    private $form;

    public function attributes(): array
    {
        /** @var Collection<FormField> */
        $form_fields = $this->form->form_fields;

        $attributes = $form_fields->flatMap(function (FormField $field) {
            return ["f$field->id" => "'{$field->title}'"];
        })->toArray();

        return $attributes;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->form = Form::find($this->route('report'));

        return $this->form ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Collection<FormField> */
        $form_fields = $this->form->form_fields;

        $rules = [];

        foreach ($form_fields as $field) {
            $field_rules = [];

            // if ($field->required) {
            //     if ($field->type === FormField::TYPE_FILE) {
            //         $record = Route::current()->parameter('record') ?? 0;
            //         $field_data = $field->field_data->where('record', $record)->first();
            //         $data = $field_data?->data;

            //         if (! $data) {
            //             $field_rules[] = 'required';
            //         }
            //     } else {
            //         $field_rules[] = 'required';
            //     }
            // } else {
            //     $field_rules[] = 'nullable';
            // }
            $field_rules[] = 'nullable';

            /** @var FormField $field */
            if ($field->type === FormField::TYPE_CHECKBOX) {
                $field_rules[] = 'array';

                $accepted_values = array_map(function ($item) {
                    return $item->id;
                }, json_decode($field->listvalues));
                $rules["f{$field->id}.*"] = Rule::in($accepted_values);
            } elseif ($field->type === FormField::TYPE_DATE) {
                $field_rules[] = 'date';
            } elseif ($field->type === FormField::TYPE_EMAIL) {
                $field_rules[] = 'email';
            } elseif ($field->type === FormField::TYPE_FILE) {
                $field_rules[] = 'file';
                $field_options = json_decode($field->options);
                $accepted_types = [];
                if ($field_options->filetype->value === '-1') {
                    $accepted_types = explode(
                        ',',
                        str_replace(['*', '.'], '', $field_options->filetype->custom_value)
                    );
                } else {
                    $accepted_filetype = AcceptedFiletype::find($field_options->filetype->value);
                    if (! $accepted_filetype) {
                        throw ValidationException::withMessages([
                            "f{$field->id}" => ['Άκυρος τύπος αρχείου στη βάση'],
                        ]);
                    }

                    $accepted_types = explode(
                        ',',
                        str_replace(['*', '.'], '', $accepted_filetype->extension)
                    );
                }

                $field_rules[] = File::types($accepted_types);
            } elseif ($field->type === FormField::TYPE_NUMBER) {
                $field_rules[] = 'numeric';
            } elseif ($field->type === FormField::TYPE_RADIO_BUTTON) {
                $field_rules[] = 'integer';
                $accepted_values = array_map(function ($item) {
                    return $item->id;
                }, json_decode($field->listvalues));
                $field_rules[] = Rule::in($accepted_values);
            } elseif ($field->type === FormField::TYPE_SELECT) {
                $field_rules[] = 'integer';
                $accepted_values = array_map(function ($item) {
                    return $item->id;
                }, json_decode($field->listvalues));
                if (! $field->required) {
                    $accepted_values[] = '-1';
                }
                $field_rules[] = Rule::in($accepted_values);
            } elseif ($field->type === FormField::TYPE_TELEPHONE) {
                $field_rules[] = 'integer';
                $field_rules[] = 'digits:10';
            } elseif ($field->type === FormField::TYPE_TEXT) {
                $field_rules[] = 'string';
                $field_rules[] = 'max:65535';
            } elseif ($field->type === FormField::TYPE_TEXTAREA) {
                $field_rules[] = 'string';
                $field_rules[] = 'max:65535';
            } elseif ($field->type === FormField::TYPE_URL) {
                $field_rules[] = 'url';
                $field_rules[] = 'max:65535';
            }

            $rules["f{$field->id}"] = $field_rules;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}
