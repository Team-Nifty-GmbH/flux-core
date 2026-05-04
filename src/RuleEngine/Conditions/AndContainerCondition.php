<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\RuleScope;

class AndContainerCondition implements ConditionInterface
{
    public static function type(): string
    {
        return 'and_container';
    }

    public static function label(): string
    {
        return 'AND';
    }

    public static function group(): string
    {
        return 'container';
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
