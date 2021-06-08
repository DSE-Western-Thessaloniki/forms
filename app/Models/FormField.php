<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Form;
use App\Models\FormFieldData;
class FormField extends Model
{
    protected $fillable = ['sort_id'];

    // Primary key
    public $primaryKey = 'id';
    // Timestamps
    public $timestamps = true;

    public function form() {
        $this->belongsTo(Form::class);
    }

    public function field_data() {
        $this->hasMany(FormFieldData::class);
    }
}
