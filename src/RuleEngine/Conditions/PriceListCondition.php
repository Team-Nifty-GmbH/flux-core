<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Concerns\ComparesValues;
use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\PriceScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class PriceListCondition implements ConditionInterface
{
    use ComparesValues;

    public array $price_list_ids = [];

    public string $operator = 'in';

    public static function type(): string
    {
        return 'price_list';
    }

    public static function label(): string
    {
        return 'Price List';
    }

    public static function group(): string
    {
        return 'other';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'price_list_ids',
                'type' => 'multiselect',
                'label' => 'Price Lists',
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
        if (! $scope instanceof PriceScope || $scope->priceList === null) {
            return false;
        }

        return static::compare($scope->priceList->getKey(), $this->operator, $this->price_list_ids);
    }
}
