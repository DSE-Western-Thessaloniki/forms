<?php

namespace App;

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
        return $this->hasMany('App\FormField');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }
}
