<?php

namespace FluxErp\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TimeDuration implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $minutes = $value % 60;
        $hours = ($value - $minutes) / 60;

        return $hours . ':' . sprintf('%02d', $minutes);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $exploded = explode(':', $value);

        return bcadd(bcmul($exploded[0], 60), $exploded[1]);
    }
}
