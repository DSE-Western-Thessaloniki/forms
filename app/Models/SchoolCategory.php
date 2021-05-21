<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\School;

class SchoolCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function schools() {
        return $this->hasMany(School::class, 'category_id');
    }
}
