<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\OrderScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class CartHasProductCondition implements ConditionInterface
{
    public array $product_ids = [];

    public string $operator = 'in';

    public static function type(): string
    {
        return 'cart_has_product';
    }

    public static function label(): string
    {
        return 'Cart Has Product';
    }

    public static function group(): string
    {
        return 'cart';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'product_ids',
                'type' => 'multiselect',
                'label' => 'Products',
                'required' => true,
            ],
            [
                'name' => 'operator',
                'type' => 'select',
                'label' => 'Operator',
                'options' => [
                    ['value' => 'in', 'label' => 'Contains'],
                    ['value' => 'not_in', 'label' => 'Does Not Contain'],
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

        $positionProductIds = $scope->positions->pluck('product_id')->toArray();
        $hasMatch = ! empty(array_intersect($positionProductIds, $this->product_ids));

        if ($this->operator === 'not_in') {
            return ! $hasMatch;
        }

        return $hasMatch;
    }
}
