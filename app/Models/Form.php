<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Http\Traits\UsesUuid;
use App\Models\FormField;
use App\Models\FormFieldData;
use App\Models\User;
use App\Models\School;
use App\Models\SchoolCategory;

class Form extends Model
{
    use UsesUuid;
    use HasFactory;

    // Table name
    // Not really needed as Laravel takes the plural form of the model as
    // a default name for the table...
    protected $table = 'forms';
    // Primary key
    public $primaryKey = 'id';
    // Timestamps
    public $timestamps = true;

    public function form_fields() {
        return $this->hasMany(FormField::class)->orderBy('sort_id');
    }

    public function schools() {
        return $this->belongsToMany(School::class);
    }

    public function school_categories() {
        return $this->belongsToMany(SchoolCategory::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function data() {
        return $this->hasManyThrough(FormFieldData::class, FormField::class);
    }
}
