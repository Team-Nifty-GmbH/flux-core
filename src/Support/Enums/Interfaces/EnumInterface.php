<?php

namespace FluxErp\Support\Enums\Interfaces;

interface EnumInterface
{
    public static function cases(): array;

    public static function from(int|string $value): object;

    public static function tryFrom(int|string|null $value): ?object;
}
