<?php

namespace App\Http\Traits;

use Illuminate\Support\Str;

trait UsesUuid
{
  protected static function bootUsesUuid() {
    static::creating(function ($model): void {
      if (! $model->getKey()) {
        $model->{$model->getKeyName()} = (string) Str::orderedUuid();
      }
    });
  }

  public function getIncrementing(): bool
  {
      return false;
  }

  public function getKeyType(): string
  {
      return 'string';
  }
}
