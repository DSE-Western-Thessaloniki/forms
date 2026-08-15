<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FormFieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

#[Fillable('sort_id', 'required', 'options')]
class FormField extends Model
{
    /** @use HasFactory<FormFieldFactory> */
    use HasFactory;

    // Primary key
    public $primaryKey = 'id';

    // Timestamps
    public $timestamps = true;

    // Τύποι πεδίων
    const int TYPE_TEXT = 0;

    const int TYPE_TEXTAREA = 1;

    const int TYPE_RADIO_BUTTON = 2;

    const int TYPE_CHECKBOX = 3;

    const int TYPE_SELECT = 4;

    const int TYPE_FILE = 5;

    const int TYPE_DATE = 6;

    const int TYPE_NUMBER = 7;

    const int TYPE_TELEPHONE = 8;

    const int TYPE_EMAIL = 9;

    const int TYPE_URL = 10;

    const int TYPE_LIST = 11;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'object',
        ];
    }

    /**
     * @return BelongsTo<Form,$this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * @return HasMany<FormFieldData,$this>
     */
    public function field_data(): HasMany
    {
        return $this->hasMany(FormFieldData::class);
    }

    public function readonly(): bool
    {
        return $this->options->readonly ?? false;
    }

    public static function fromRequest(Request $request, Form $form): void
    {
        $formfield = $request->input('field');

        foreach (array_keys($formfield) as $key) {
            $field = new FormField;
            $defaultSort = is_numeric($key) ? (int) $key : 0;
            $field->sort_id = self::sanitizeSortId($formfield[$key]['sort_id'] ?? null, $defaultSort + 1);
            $field->title = $formfield[$key]['title'];

            // Αν βρεις προκαθορισμένη λίστα μετέτρεψέ την σε απλή λίστα επιλογών
            if ($formfield[$key]['type'] == FormField::TYPE_LIST) {
                $selection_list = SelectionList::find($formfield[$key]['selection_list']);

                $field->type = FormField::TYPE_SELECT;
                $field->options = $formfield[$key]['options'] ?? new \stdClass;
                $field->listvalues = $selection_list->data;
            } else {
                $field->type = $formfield[$key]['type'];
                $field->options = $formfield[$key]['options'] ?? new \stdClass;
                $field->listvalues = $formfield[$key]['values'] ?? '';
            }
            $field->required = $formfield[$key]['required'] === 'true';
            $form->form_fields()->save($field);
        }

        self::normalizeSortIds($form);
    }

    public static function updateFromRequest(Request $request, Form $form): void
    {
        $formfield = $request->input('field');

        foreach (array_keys($formfield) as $key) {
            $field = $form->form_fields()->firstOrNew(['id' => $key]);
            $defaultSort = is_numeric($key) ? (int) $key : 0;
            $field->sort_id = self::sanitizeSortId($formfield[$key]['sort_id'] ?? null, $defaultSort + 1);
            $field->title = $formfield[$key]['title'];

            if ($formfield[$key]['type'] == FormField::TYPE_LIST) {
                $selection_list = SelectionList::find($formfield[$key]['selection_list']);

                $field->type = FormField::TYPE_SELECT;
                $field->options = $formfield[$key]['options'] ?? new \stdClass;
                $field->listvalues = $selection_list->data;
            } else {
                $field->type = $formfield[$key]['type'];
                $field->options = $formfield[$key]['options'] ?? new \stdClass;
                $field->listvalues = $formfield[$key]['values'] ?? '';
            }

            $field->required = $formfield[$key]['required'] === 'true';
            $form->form_fields()->save($field);
        }

        self::normalizeSortIds($form);
    }

    /**
     * Ensure sort_id values are sequential (1..N) to avoid duplicates/gaps
     * which can occur when new fields are added but sort IDs are not updated.
     */
    private static function normalizeSortIds(Form $form): void
    {
        $fields = $form->form_fields()->orderBy('sort_id')->orderBy('id')->get();
        $sortId = 1;

        foreach ($fields as $field) {
            if ($field->sort_id !== $sortId) {
                $field->sort_id = $sortId;
                $field->save();
            }
            $sortId++;
        }
    }

    private static function sanitizeSortId(mixed $value, int $default): int
    {
        if (is_numeric($value)) {
            $int = (int) $value;
            if ($int > 0) {
                return $int;
            }
        }

        return $default > 0 ? $default : 1;
    }
}
