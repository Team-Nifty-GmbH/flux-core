<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Concerns\ComparesValues;
use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\OrderScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class CartAmountCondition implements ConditionInterface
{
    use ComparesValues;

    public float $amount = 0;

    public string $operator = '>=';

    public static function type(): string
    {
        return 'cart_amount';
    }

    public static function label(): string
    {
        return 'Cart Amount';
    }

    public static function group(): string
    {
        return 'cart';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'amount',
                'type' => 'number',
                'label' => 'Amount',
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

        $total = $scope->positions->sum('total_net_price');

        return static::compare($total, $this->operator, $this->amount);
    }
}
