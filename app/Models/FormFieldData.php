<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\FormField;
use App\Models\School;

class FormFieldData extends Model
{
    public function form_fields() {
        $this->belongsTo(FormField::class);
    }

    public function schools() {
        $this->belongsTo(School::class);
    }
}
