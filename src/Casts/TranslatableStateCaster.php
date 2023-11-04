<?php

namespace FluxErp\Casts;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateCaster;

class TranslatableStateCaster extends StateCaster
{
    public function __construct(string $baseStateClass)
    {
        parent::__construct($baseStateClass);
    }

    public function get($model, string $key, $value, array $attributes): ?State
    {
        return parent::get($model, $key, __($value), $attributes);
    }
}
