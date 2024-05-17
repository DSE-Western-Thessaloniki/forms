<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = ['sort_id', 'required', 'options'];

    // Primary key
    public $primaryKey = 'id';

    // Timestamps
    public $timestamps = true;

    // Τύποι πεδίων
    const TYPE_TEXT = 0;

    const TYPE_TEXTAREA = 1;

    const TYPE_RADIO_BUTTON = 2;

    const TYPE_CHECKBOX = 3;

    const TYPE_SELECT = 4;

    const TYPE_FILE = 5;

    const TYPE_DATE = 6;

    const TYPE_NUMBER = 7;

    const TYPE_TELEPHONE = 8;

    const TYPE_EMAIL = 9;

    const TYPE_URL = 10;

    const TYPE_LIST = 11;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function field_data()
    {
        return $this->hasMany(FormFieldData::class);
    }

    /**
     * @return array{multiple:bool}
     */
    public function field_options()
    {
        return json_decode($this->options);
    }

    public static function fromRequest(Request $request, Form $form): void
    {
        $formfield = $request->input('field');
        foreach (array_keys($formfield) as $key) {
            $field = new FormField;
            $field->sort_id = $formfield[$key]['sort_id'];
            $field->title = $formfield[$key]['title'];

            // Αν βρεις προκαθορισμένη λίστα μετέτρεψέ την σε απλή λίστα επιλογών
            if ($formfield[$key]['type'] == FormField::TYPE_LIST) {
                $selection_list = SelectionList::find($formfield[$key]['selection_list']);

                $field->type = FormField::TYPE_SELECT;
                $field->options = json_encode($formfield[$key]['options'] ?? new \stdClass());
                $field->listvalues = $selection_list->data;
            } else {
                $field->type = $formfield[$key]['type'];
                $field->options = json_encode($formfield[$key]['options'] ?? new \stdClass());
                $field->listvalues = $formfield[$key]['values'] ?? '';
            }
            $field->required = $formfield[$key]['required'] === 'true' ? true : false;
            $form->form_fields()->save($field);
        }
    }

    public static function updateFromRequest(Request $request, Form $form): void
    {
        $formfield = $request->input('field');

        foreach (array_keys($formfield) as $key) {
            $field = $form->form_fields()->firstOrNew(['id' => $key]);
            $field->sort_id = $formfield[$key]['sort_id'] ?? $key;
            $field->title = $formfield[$key]['title'];

            if ($formfield[$key]['type'] == FormField::TYPE_LIST) {
                $selection_list = SelectionList::find($formfield[$key]['selection_list']);

                $field->type = FormField::TYPE_SELECT;
                $field->options = json_encode($formfield[$key]['options'] ?? new \stdClass());
                $field->listvalues = $selection_list->data;
            } else {
                $field->type = $formfield[$key]['type'];
                $field->options = json_encode($formfield[$key]['options'] ?? new \stdClass());
                $field->listvalues = $formfield[$key]['values'] ?? '';
            }

            $field->required = $formfield[$key]['required'] === 'true' ? true : false;
            $form->form_fields()->save($field);
        }
    }
}
