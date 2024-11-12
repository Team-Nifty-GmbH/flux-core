<?php

namespace FluxErp\Casts;

use TeamNiftyGmbH\DataTable\Casts\Money as BaseMoney;

class Money extends BaseMoney
{
    public function get($model, string $key, $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        return parent::get($model, $key, $value, $attributes);
    }
}
