<?php

namespace FluxErp\Tests\Support\RuleEngine;

use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\RuleScope;

class AlwaysTrueCondition implements ConditionInterface
{
    public static function type(): string
    {
        return 'always_true';
    }

    public static function label(): string
    {
        return 'Always True';
    }

    public static function group(): string
    {
        return 'test';
    }

    public static function schema(): array
    {
        return [];
    }

    public static function supportedScopes(): array
    {
        return [];
    }

    public function match(RuleScope $scope): bool
    {
        return true;
    }
}
