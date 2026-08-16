<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FormFieldDataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_id', 'teacher_id', 'other_teacher_id', 'data', 'record', 'updated_at'])]
final class FormFieldData extends Model
{
    /** @use HasFactory<FormFieldDataFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<FormField,$this>
     */
    public function form_field(): BelongsTo
    {
        return $this->belongsTo(FormField::class);
    }

    /**
     * @return BelongsTo<School,$this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * @return BelongsTo<Teacher,$this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * @return BelongsTo<OtherTeacher,$this>
     */
    public function other_teacher(): BelongsTo
    {
        return $this->belongsTo(OtherTeacher::class, 'other_teacher_id');
    }

    public function dataToString(?FormField $field = null): string
    {
        if (! $field instanceof FormField) {
            $field = $this->form_field;
        }

        if ($this->data === null || $this->data === '') {
            return '';
        }

        if ($field->type === FormField::TYPE_RADIO_BUTTON || $field->type === FormField::TYPE_SELECT) {
            $selections = json_decode($field->listvalues);

            // Μετέτρεψε την επιλογή σε τιμή
            $result = '';

            foreach ($selections as $selection) {
                if ($selection->id === intval($this->data)) {
                    $result = $selection->value;
                }
            }

            return $result;
        }

        if ($field->type === FormField::TYPE_CHECKBOX) {
            $selections = json_decode($field->listvalues);

            // Μπορεί να έχουμε επιλέξει παραπάνω από ένα
            $result = '';
            $data = json_decode($this->data);
            $i = 0;
            foreach ($data as $item) {
                foreach ($selections as $selection) {
                    if ($selection->id === intval($item)) {
                        if ($i === 0) {
                            $result = $selection->value;
                        } else {
                            $result .= ', '.$selection->value;
                        }
                    }
                }
                $i++;
            }

            return $result;
        }

        return $this->data;
    }
}
