<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\PriceScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class ContactDiscountGroupCondition implements ConditionInterface
{
    public array $group_ids = [];

    public string $operator = 'in';

    public static function type(): string
    {
        return 'contact_discount_group';
    }

    public static function label(): string
    {
        return 'Contact Discount Group';
    }

    public static function group(): string
    {
        return 'customer';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'group_ids',
                'type' => 'multiselect',
                'label' => 'Discount Groups',
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
        if (! $scope instanceof PriceScope || $scope->contact === null) {
            return false;
        }

        $scope->contact->loadMissing('discountGroups');
        $contactGroupIds = $scope->contact->discountGroups->pluck('id')->toArray();
        $intersection = array_intersect($contactGroupIds, $this->group_ids);

        if ($this->operator === 'not_in') {
            return empty($intersection);
        }

        return ! empty($intersection);
    }
}
