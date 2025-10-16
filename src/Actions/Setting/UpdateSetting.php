<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\Setting\UpdateSettingRuleset;
use FluxErp\Settings\FluxSettings;
use Illuminate\Support\Arr;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Spatie\LaravelSettings\SettingsConfig;

class UpdateSetting extends FluxAction
{
    public static function models(): array
    {
        return [];
    }

    protected function getRulesets(): string|array
    {
        return UpdateSettingRuleset::class;
    }

    public function performAction(): FluxSettings
    {
        $settings = app($this->getData('settings_class'));

        $properties = $settings
            ->toCollection()
            ->keys()
            ->all();

        $settings->fill(Arr::only($this->getData(), $properties));
        $settings->save();

        return $settings;
    }

    public function setRulesFromRulesets(): static
    {
        if ($settingsClass = $this->getData('settings_class')) {
            $this->mergeRules($this->validateSettingsProperties($settingsClass));
        }

        return parent::setRulesFromRulesets();
    }

    protected function extractRulesFromType(?ReflectionType $type): array
    {
        if (! $type) {
            return [
                'sometimes',
                'required',
            ];
        }

        $rules = match (true) {
            $type instanceof ReflectionUnionType => $this->getRulesForUnionType($type),
            $type instanceof ReflectionIntersectionType => ['sometimes', 'required', 'object'],
            $type instanceof ReflectionNamedType => array_merge(
                ['sometimes', 'required'],
                $this->getRulesForNamedType($type)
            ),
            default => ['sometimes', 'required'],
        };

        if ($type->allowsNull()) {
            return array_merge(['nullable'], array_diff($rules, ['sometimes', 'required']));
        }

        return $rules;
    }

    protected function getRulesForNamedType(ReflectionNamedType $type): array
    {
        if (! $type->isBuiltin()) {
            return ['object'];
        }

        return match ($type->getName()) {
            'bool', 'boolean' => ['boolean'],
            'int', 'integer' => ['integer'],
            'float', 'double' => ['numeric'],
            'string' => ['string', 'max:255'],
            'array' => ['array'],
            default => [],
        };
    }

    protected function getRulesForUnionType(ReflectionUnionType $type): array
    {
        $rules = ['required'];

        foreach ($type->getTypes() as $unionType) {
            if ($unionType instanceof ReflectionNamedType) {
                $rules = array_merge($rules, $this->getRulesForNamedType($unionType));
            }
        }

        return array_values(array_unique($rules));
    }

    protected function validateSettingsProperties(string $settingsClass): array
    {
        $config = app(SettingsConfig::class, ['settingsClass' => $settingsClass]);
        $rules = [];

        foreach ($config->getReflectedProperties() as $propertyName => $reflectionProperty) {
            $rules[$propertyName] = $this->extractRulesFromType($reflectionProperty->getType());
        }

        return $rules;
    }
}
