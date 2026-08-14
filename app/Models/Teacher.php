<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['surname', 'name', 'am', 'afm', 'active'])]
class Teacher extends Model
{
    use HasFactory;
}
