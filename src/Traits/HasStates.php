<?php

namespace FluxErp\Traits;

use Spatie\ModelStates\HasStates as BaseHasStates;
use Spatie\ModelStates\State;

trait HasStates
{
    use BaseHasStates;

    public function initializeHasStates(): void
    {
        $this->setStateDefaults();

        foreach ($this->getAttributes() as $key => $value) {
            if (is_string($value) && is_subclass_of($value, State::class)) {
                $this->attributes[$key] = $value::getMorphClass();
            }
        }
    }
}
