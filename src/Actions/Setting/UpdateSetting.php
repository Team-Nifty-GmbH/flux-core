<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\Setting\UpdateSettingRuleset;
use ReflectionNamedType;
use Spatie\LaravelSettings\Settings;
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

    public function performAction(): Settings
    {
        $settingsClass = $this->data['settings_class'];
        $settings = app($settingsClass);

        $properties = $settings->toCollection()->keys()->all();
        $updateData = collect($this->data)
            ->only($properties)
            ->all();

        $settings->fill($updateData);
        $settings->save();

        return $settings;
    }

    public function setRulesFromRulesets(): static
    {
        $this->mergeRules($this->validateSettingsProperties($this->getData('settings_class')));

        return parent::setRulesFromRulesets();
    }

    protected function validateSettingsProperties(string $settingsClass): array
    {
        $config = new SettingsConfig($settingsClass);
        $rules = [];

        foreach ($config->getReflectedProperties() as $propertyName => $reflectionProperty) {
            $propertyRules = ['required'];

            $type = $reflectionProperty->getType();

            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $rules[$propertyName] = $propertyRules;

                continue;
            }

            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();

                switch ($typeName) {
                    case 'bool':
                    case 'boolean':
                        $propertyRules[] = 'boolean';
                        break;
                    case 'int':
                    case 'integer':
                        $propertyRules[] = 'integer';
                        break;
                    case 'float':
                    case 'double':
                        $propertyRules[] = 'numeric';
                        break;
                    case 'string':
                        $propertyRules[] = 'string';
                        $propertyRules[] = 'max:255';
                        break;
                    case 'array':
                        $propertyRules[] = 'array';
                        break;
                }

                if ($type->allowsNull()) {
                    $propertyRules = array_filter($propertyRules, fn ($rule) => $rule !== 'required');
                    $propertyRules[] = 'nullable';
                }
            }

            $rules[$propertyName] = $propertyRules;
        }

        return $rules;
    }
}
