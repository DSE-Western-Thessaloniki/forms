<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OtherTeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employeenumber', 'name', 'email'])]
class OtherTeacher extends Model
{
    /** @use HasFactory<OtherTeacherFactory> */
    use HasFactory;
}
