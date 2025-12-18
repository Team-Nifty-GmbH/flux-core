<?php

namespace FluxErp\Support\Enums\Traits;

use Illuminate\Database\Eloquent\Model;

trait IsCastable
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?object
    {
        return resolve_static(static::class, 'tryFrom', ['value' => $value]);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): int|string|null
    {
        if (is_object($value) && property_exists($value, 'value')) {
            return $value->value;
        } elseif (is_int($value) || is_string($value)) {
            return resolve_static(static::class, 'tryFrom', ['value' => $value])
                ?->value;
        } else {
            return null;
        }
    }
}
