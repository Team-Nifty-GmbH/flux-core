<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Concerns\ComparesValues;
use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\PriceScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class LineItemQuantityCondition implements ConditionInterface
{
    use ComparesValues;

    public int $quantity = 0;

    public string $operator = '>=';

    public static function type(): string
    {
        return 'line_item_quantity';
    }

    public static function label(): string
    {
        return 'Line Item Quantity';
    }

    public static function group(): string
    {
        return 'product';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'quantity',
                'type' => 'number',
                'label' => 'Quantity',
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
        return [PriceScope::class];
    }

    public function match(RuleScope $scope): bool
    {
        if (! $scope instanceof PriceScope || $scope->quantity === null) {
            return false;
        }

        return static::compare($scope->quantity, $this->operator, $this->quantity);
    }
}
