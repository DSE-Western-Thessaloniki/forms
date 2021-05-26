<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\Form;
use App\Models\User;
use App\Models\SchoolCategory;

class School extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'code', 'email', 'password', 'active', 'updated_by', 'category_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function forms() {
        return $this->belongsToMany(Form::class);
    }

    public function categories() {
        return $this->belongsToMany(SchoolCategory::class, 'school_category_school');
    }
}
