<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AcceptedFiletypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['extension', 'description'])]
class AcceptedFiletype extends Model
{
    /** @use HasFactory<AcceptedFiletypeFactory> */
    use HasFactory;
}
