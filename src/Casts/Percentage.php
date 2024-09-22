<?php

namespace FluxErp\Casts;

use Illuminate\Database\Eloquent\Model;
use TeamNiftyGmbH\DataTable\Casts\Percentage as BasePercentage;

class Percentage extends BasePercentage
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return is_null($value) ? $value : bcmul($value, 100, 2);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return is_null($value) ? $value : bcdiv($value, 100, 4);
    }
}
