<?php

namespace FluxErp\RuleEngine\Conditions;

use FluxErp\RuleEngine\Concerns\ComparesValues;
use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\PriceScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

class ContactCustomFieldCondition implements ConditionInterface
{
    use ComparesValues;

    public ?string $field = null;

    public string $operator = '=';

    public mixed $value = null;

    public static function type(): string
    {
        return 'contact_custom_field';
    }

    public static function label(): string
    {
        return 'Contact Custom Field';
    }

    public static function group(): string
    {
        return 'customer';
    }

    public static function schema(): array
    {
        return [
            [
                'name' => 'field',
                'type' => 'text',
                'label' => 'Field',
                'required' => true,
            ],
            [
                'name' => 'operator',
                'type' => 'select',
                'label' => 'Operator',
                'options' => [
                    ['value' => '=', 'label' => 'Equals'],
                    ['value' => '!=', 'label' => 'Not Equals'],
                    ['value' => '>', 'label' => 'Greater Than'],
                    ['value' => '<', 'label' => 'Less Than'],
                    ['value' => '>=', 'label' => 'Greater Than or Equal'],
                    ['value' => '<=', 'label' => 'Less Than or Equal'],
                    ['value' => 'in', 'label' => 'In'],
                    ['value' => 'not_in', 'label' => 'Not In'],
                ],
                'required' => true,
            ],
            [
                'name' => 'value',
                'type' => 'text',
                'label' => 'Value',
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
        if (! $scope instanceof PriceScope || $scope->contact === null || $this->field === null) {
            return false;
        }

        $actual = data_get($scope->contact, $this->field);

        return static::compare($actual, $this->operator, $this->value);
    }
}
