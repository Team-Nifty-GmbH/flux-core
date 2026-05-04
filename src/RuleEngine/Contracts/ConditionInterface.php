<?php

namespace FluxErp\RuleEngine\Contracts;

use FluxErp\RuleEngine\Scopes\RuleScope;

interface ConditionInterface
{
    public static function type(): string;

    public static function label(): string;

    public static function group(): string;

    public static function schema(): array;

    public static function supportedScopes(): array;

    public function match(RuleScope $scope): bool;
}
