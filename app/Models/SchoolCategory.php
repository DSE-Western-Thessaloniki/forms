<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Form;
use App\Models\School;

class SchoolCategory extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function schools() {
        return $this->belongsToMany(School::class, 'school_category_school');
    }

    public function forms() {
        return $this->belongsToMany(Form::class);
    }
}
