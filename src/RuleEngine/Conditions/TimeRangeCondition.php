<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\RuleScope;

class TimeRangeCondition implements ConditionInterface
{
    public ?string $from = null;

    public ?string $to = null;

    public static function type(): string
    {
        return 'time_range';
    }

    public static function label(): string
    {
        return 'Time Range';
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
                'type' => 'time',
                'label' => 'From',
                'required' => false,
            ],
            [
                'name' => 'to',
                'type' => 'time',
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
        $currentTime = $scope->now->format('H:i');

        if ($this->from !== null && $currentTime < $this->from) {
            return false;
        }

        if ($this->to !== null && $currentTime > $this->to) {
            return false;
        }

        return true;
    }
}
