<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\FormField;
use App\Models\School;

class FormFieldData extends Model
{
    use HasFactory;

    protected $fillable = ['school_id', 'teacher_id', 'other_teacher_id', 'data', 'record', 'updated_at'];

    public function form_field() {
        return $this->belongsTo(FormField::class);
    }

    public function school() {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function teacher() {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function other_teacher() {
        return $this->belongsTo(OtherTeacher::class, 'other_teacher_id');
    }

    /**
     * Returns the filename stored in storage and the real filename of the file
     * @return array{array{filename: string, real_filename: string}}
     */
    public function file() {
        return json_decode($this->data);
    }
}
