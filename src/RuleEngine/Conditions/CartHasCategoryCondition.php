<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\Models\Product;
use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\OrderScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class CartHasCategoryCondition implements ConditionInterface
{
    public array $category_ids = [];

    public string $operator = 'in';

    public static function type(): string
    {
        return 'cart_has_category';
    }

    public static function label(): string
    {
        return 'Cart Has Category';
    }

    public static function group(): string
    {
        return 'cart';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'category_ids',
                'type' => 'multiselect',
                'label' => 'Categories',
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

        $productIds = $scope->positions->pluck('product_id')->filter()->toArray();

        if (empty($productIds)) {
            return $this->operator === 'not_in';
        }

        $hasMatch = resolve_static(Product::class, 'query')
            ->whereKey($productIds)
            ->whereHas('categories', function ($query): void {
                $query->whereKey($this->category_ids);
            })
            ->exists();

        if ($this->operator === 'not_in') {
            return ! $hasMatch;
        }

        return $hasMatch;
    }
}
