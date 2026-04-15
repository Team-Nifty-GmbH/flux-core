<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\RuleScope;

class DateRangeCondition implements ConditionInterface
{
    public ?string $from = null;

    public ?string $to = null;

    public static function type(): string
    {
        return 'date_range';
    }

    public static function label(): string
    {
        return 'Date Range';
    }

    public static function group(): string
    {
        return 'time';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'from',
                'type' => 'date',
                'label' => 'From',
                'required' => false,
            ],
            [
                'name' => 'to',
                'type' => 'date',
                'label' => 'To',
                'required' => false,
            ],
        ];
    }

    public static function supportedScopes(): array
    {
        return [];
    }

    public function match(RuleScope $scope): bool
    {
        $now = $scope->now->copy()->startOfDay();

        if ($this->from !== null && $now->lt($this->from)) {
            return false;
        }

        if ($this->to !== null && $now->gt($this->to)) {
            return false;
        }

        return true;
    }
}
