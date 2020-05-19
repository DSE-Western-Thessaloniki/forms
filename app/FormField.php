<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    // Table name
    // Not really needed as Laravel takes the plural form of the model as
    // a default name for the table...
    protected $table = 'formfields';
    // Primary key
    public $primaryKey = 'id';
    // Timestamps
    public $timestamps = true;

    public function form() {
        $this->belongsTo('App\Form');
    }
}
