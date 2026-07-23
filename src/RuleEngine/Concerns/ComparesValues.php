<?php

namespace FluxErp\RuleEngine\Concerns;

trait ComparesValues
{
    protected static function compare(mixed $actual, string $operator, mixed $expected): bool
    {
        return match ($operator) {
            '=' => $actual === $expected,
            '~' => $actual == $expected,
            '!=' => $actual != $expected,
            '>' => $actual > $expected,
            '<' => $actual < $expected,
            '>=' => $actual >= $expected,
            '<=' => $actual <= $expected,
            'in' => is_array($expected) && in_array($actual, $expected),
            'not_in' => is_array($expected) && ! in_array($actual, $expected),
            default => false,
        };
    }
}
