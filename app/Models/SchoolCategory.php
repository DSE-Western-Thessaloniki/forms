<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SchoolCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable('name')]
final class SchoolCategory extends Model
{
    /** @use HasFactory<SchoolCategoryFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<School,$this>
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_category_school');
    }

    /**
     * @return BelongsToMany<Form,$this>
     */
    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class);
    }
}
