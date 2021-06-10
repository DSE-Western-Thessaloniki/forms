<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\FormField;
use App\Models\School;

class FormFieldData extends Model
{
    protected $fillable = ['school_id', 'data'];

    public function form_fields() {
        return $this->belongsTo(FormField::class);
    }

    public function schools() {
        return $this->belongsTo(School::class, 'school_id');
    }
}
