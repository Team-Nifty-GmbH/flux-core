<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\RuleScope;

class DayOfWeekCondition implements ConditionInterface
{
    public array $days = [];

    public static function type(): string
    {
        return 'day_of_week';
    }

    public static function label(): string
    {
        return 'Day of Week';
    }

    public static function group(): string
    {
        return 'time';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'days',
                'type' => 'multiselect',
                'label' => 'Days',
                'options' => [
                    ['value' => 1, 'label' => 'Monday'],
                    ['value' => 2, 'label' => 'Tuesday'],
                    ['value' => 3, 'label' => 'Wednesday'],
                    ['value' => 4, 'label' => 'Thursday'],
                    ['value' => 5, 'label' => 'Friday'],
                    ['value' => 6, 'label' => 'Saturday'],
                    ['value' => 7, 'label' => 'Sunday'],
                ],
                'required' => true,
            ],
        ];
    }

    public static function supportedScopes(): array
    {
        return [];
    }

    public function match(RuleScope $scope): bool
    {
        if (empty($this->days)) {
            return false;
        }

        return in_array($scope->now->dayOfWeekIso, $this->days);
    }
}
