<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    // Table name
    // Not really needed as Laravel takes the plural form of the model as
    // a default name for the table...
    protected $table = 'levels';
    // Primary key
    public $primaryKey = 'id';
    // Timestamps
    public $timestamps = true;
}
