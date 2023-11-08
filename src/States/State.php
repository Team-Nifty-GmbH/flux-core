<?php

namespace FluxErp\States;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\ModelStates\State as BaseState;

abstract class State extends BaseState implements Arrayable
{
    public function toArray(): array|string
    {
        return $this->__toString();
    }
}
