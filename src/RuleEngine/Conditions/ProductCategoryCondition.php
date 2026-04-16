<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\PriceScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class ProductCategoryCondition implements ConditionInterface
{
    public array $category_ids = [];

    public string $operator = 'in';

    public static function type(): string
    {
        return 'product_category';
    }

    public static function label(): string
    {
        return 'Product Category';
    }

    public static function group(): string
    {
        return 'product';
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
                    ['value' => 'in', 'label' => 'In'],
                    ['value' => 'not_in', 'label' => 'Not In'],
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
        if (! $scope instanceof PriceScope || $scope->product === null) {
            return false;
        }

        $scope->product->loadMissing('categories:id');
        $productCategoryIds = $scope->product->categories->pluck('id')->toArray();
        $intersection = array_intersect($productCategoryIds, $this->category_ids);

        if ($this->operator === 'not_in') {
            return empty($intersection);
        }

        return ! empty($intersection);
    }
}
