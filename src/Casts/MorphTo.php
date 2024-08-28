<?php

namespace FluxErp\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class MorphTo implements CastsAttributes
{
    public function __construct(public string $value) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $model = morph_to(type: $value, returnBuilder: true);

        return Cache::remember(
            'morph_to:' . $value,
            86400,
            function () use ($model, $value) {
                if ($this->value && $model) {
                    try {
                        return $model->value($this->value);
                    } catch (QueryException) {
                        return $value;
                    }
                } else {
                    return $model?->first() ?? $value;
                }
            }
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        if ((! is_string($value) && ! $value instanceof Model) || ! $value) {
            return null;
        }

        return is_string($value) ? $value : $value->getMorphClass() . ':' . $value->getKey();
    }
}
