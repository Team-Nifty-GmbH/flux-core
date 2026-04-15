<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Concerns\ComparesValues;
use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\OrderScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class CartLineItemCountCondition implements ConditionInterface
{
    use ComparesValues;

    public int $count = 0;

    public string $operator = '>=';

    public static function type(): string
    {
        return 'cart_line_item_count';
    }

    public static function label(): string
    {
        return 'Cart Line Item Count';
    }

    public static function group(): string
    {
        return 'cart';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'count',
                'type' => 'number',
                'label' => 'Count',
                'required' => true,
            ],
            [
                'name' => 'operator',
                'type' => 'select',
                'label' => 'Operator',
                'options' => [
                    ['value' => '>=', 'label' => 'Greater Than or Equal'],
                    ['value' => '>', 'label' => 'Greater Than'],
                    ['value' => '<=', 'label' => 'Less Than or Equal'],
                    ['value' => '<', 'label' => 'Less Than'],
                    ['value' => '=', 'label' => 'Equals'],
                    ['value' => '!=', 'label' => 'Not Equals'],
                ],
                'required' => true,
            ],
        ];
    }

    public static function supportedScopes(): array
    {
        return [OrderScope::class];
    }

    public function match(RuleScope $scope): bool
    {
        if (! $scope instanceof OrderScope) {
            return false;
        }

        return static::compare($scope->positions->count(), $this->operator, $this->count);
    }
}
