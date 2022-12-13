<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherTeacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'employeenumber',
        'name',
        'email'
    ];
}
