<?php

namespace FluxErp\States;

abstract class EndableState extends State
{
    public static bool $isEndState = false;

    /**
     * The keys of the states that end the lifecycle.
     *
     * @return array<int, string>
     */
    public static function endStateKeys(): array
    {
        return static::all()
            ->filter(fn (string $state): bool => $state::$isEndState)
            ->keys()
            ->toArray();
    }
}
