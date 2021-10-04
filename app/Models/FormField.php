<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Form;
use App\Models\FormFieldData;
class FormField extends Model
{
    use HasFactory;

    protected $fillable = ['sort_id'];

    // Primary key
    public $primaryKey = 'id';
    // Timestamps
    public $timestamps = true;

    public function form() {
        return $this->belongsTo(Form::class);
    }

    public function field_data() {
        return $this->hasMany(FormFieldData::class);
    }
}
