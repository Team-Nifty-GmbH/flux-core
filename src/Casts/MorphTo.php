<?php

namespace FluxErp\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MorphTo implements CastsAttributes
{
    public function __construct(public string $value) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $model = morph_to(type: $value, returnBuilder: true);

        return $this->value ? $model->value($this->value) : $model->first();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return is_string($value) ? $value : $value->getMorphClass() . ':' . $value->getKey();
    }
}
