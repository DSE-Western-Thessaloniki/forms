<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'code', 'email', 'telephone',
    'password', 'active', 'updated_by', 'category_id'])]
#[Hidden(['password', 'remember_token'])]
final class School extends Authenticatable
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User,$this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsToMany<Form,$this>
     */
    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class);
    }

    /**
     * @return BelongsToMany<SchoolCategory,$this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(SchoolCategory::class, 'school_category_school');
    }

    /**
     * @return HasMany<FormFieldData,$this>
     */
    public function field_data(): HasMany
    {
        return $this->hasMany(FormFieldData::class);
    }
}
