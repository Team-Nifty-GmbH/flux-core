<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Concerns\ComparesValues;
use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\PriceScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class ContactCondition implements ConditionInterface
{
    use ComparesValues;

    public array $contact_ids = [];

    public string $operator = 'in';

    public static function type(): string
    {
        return 'contact';
    }

    public static function label(): string
    {
        return 'Contact';
    }

    public static function group(): string
    {
        return 'customer';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'contact_ids',
                'type' => 'multiselect',
                'label' => 'Contacts',
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

        return static::compare($scope->contact->getKey(), $this->operator, $this->contact_ids);
    }
}
