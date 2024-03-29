<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Form;
use App\Models\FormFieldData;
class FormField extends Model
{
    use HasFactory;

    protected $fillable = ['sort_id', 'required'];

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

    public function form() {
        return $this->belongsTo(Form::class);
    }

    public function field_data() {
        return $this->hasMany(FormFieldData::class);
    }
}
