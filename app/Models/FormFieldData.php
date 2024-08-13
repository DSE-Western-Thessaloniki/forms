<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormFieldData extends Model
{
    use HasFactory;

    protected $fillable = ['school_id', 'teacher_id', 'other_teacher_id', 'data', 'record', 'updated_at'];

    public function form_field()
    {
        return $this->belongsTo(FormField::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function other_teacher()
    {
        return $this->belongsTo(OtherTeacher::class, 'other_teacher_id');
    }

    public function dataToString(?FormField $field = null): string
    {
        if ($field === null) {
            $field = $this->form_field;
        }

        if ($this->data === null) {
            return '';
        }

        if ($field->type == FormField::TYPE_RADIO_BUTTON || $field->type == FormField::TYPE_SELECT) {
            $selections = json_decode($field->listvalues);

            // Μετέτρεψε την επιλογή σε τιμή
            $result = '';

            foreach ($selections as $selection) {
                if ($selection->id == $this->data) {
                    $result = $selection->value;
                }
            }

            return $result;
        }

        if ($field->type == FormField::TYPE_CHECKBOX) {
            $selections = json_decode($field->listvalues);

            // Μπορεί να έχουμε επιλέξει παραπάνω από ένα
            $result = '';
            $data = json_decode($this->data);
            $i = 0;
            foreach ($data as $item) {
                foreach ($selections as $selection) {
                    if ($selection->id == $item) {
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
