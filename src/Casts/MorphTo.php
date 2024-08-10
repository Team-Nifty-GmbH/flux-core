<?php

namespace FluxErp\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
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
            'morph_to:' . $value . ($this->value ? ':' . $this->value : ''),
            86400,
            fn () => $this->value && $model ? $model->value($this->value) : $model?->first()
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return is_string($value) ? $value : $value->getMorphClass() . ':' . $value->getKey();
    }
}
