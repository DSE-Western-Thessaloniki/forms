<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    // Table name
    // Not really needed as Laravel takes the plural form of the model as
    // a default name for the table...
    protected $table = 'forms';
    // Primary key
    public $primaryKey = 'id';
    // Timestamps
    public $timestamps = true;

    public function formfields() {
        return $this->hasMany('App\Models\FormField');
    }

    public function schools() {
        return $this->belongsToMany('App\Models\School');
    }

    public function school_categories() {
        return $this->belongsToMany('App\Models\SchoolCategory');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}
