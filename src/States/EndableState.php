<?php

namespace FluxErp\States;

abstract class EndableState extends State
{
    public static bool $isEndState = false;

    /**
     * The names of the states that end the lifecycle.
     *
     * @return array<int, string>
     */
    public static function endStateNames(): array
    {
        return static::all()
            ->filter(fn (string $state): bool => $state::$isEndState)
            ->keys()
            ->toArray();
    }
}
