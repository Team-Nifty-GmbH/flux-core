<?php

namespace FluxErp\Support;

use FluxErp\Contracts\EditorButton;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class EditorManager
{
    protected static array $buttons = [];

    protected static array $variables = [];

    public static function addVariable(string|array $value, ?string $modelClass = null, ?string $path = null): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        if (! data_get(static::$variables, $morphAlias)) {
            data_set(static::$variables, $morphAlias, []);
        }

        $variablePath = implode('.', array_filter([$morphAlias, $path]));

        $current = Arr::wrap(data_get(static::$variables, $variablePath) ?? []);
        $current[] = $value;

        data_set(static::$variables, $variablePath, $current);
    }

    public static function allVariables(): array
    {
        return static::$variables;
    }

    public static function clearButtons(): void
    {
        static::$buttons = [];
    }

    public static function clearVariables(): void
    {
        static::$variables = [];
    }

    /**
     * @return array<class-string<EditorButton>>
     */
    public static function getButtons(): array
    {
        return static::$buttons;
    }

    public static function getTranslatedVariables(
        ?string $modelClass = null,
        ?string $path = null,
        bool $withGlobals = true
    ): array {
        $variables = static::getVariables($modelClass, $path, $withGlobals);
        if (is_string($variables)) {
            $variables = [$variables => $variables];
        }

        return static::translate($variables);
    }

    public static function getVariables(?string $modelClass = null, ?string $path = null, bool $withGlobals = true): string|array
    {
        $data = data_get(
            static::$variables,
            implode('.', array_filter([static::getMorphAlias($modelClass), $path])),
        ) ?? [];

        if (! $withGlobals || is_null($modelClass)) {
            return $data;
        }

        $globals = data_get(
            static::$variables,
            implode('.', array_filter([static::getMorphAlias(null), $path])),
        ) ?? [];

        return array_merge($data, $globals);
    }

    /**
     * @param  class-string<EditorButton>  $buttonClass
     */
    public static function hasButton(string $buttonClass): bool
    {
        return in_array($buttonClass, static::$buttons, true);
    }

    /**
     * @param  class-string  $buttonClass
     */
    public static function isLivewireButton(string $buttonClass): bool
    {
        return is_subclass_of($buttonClass, \Livewire\Component::class);
    }

    public static function mergeVariables(array $values, ?string $modelClass = null, ?string $path = null): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        if (! data_get(static::$variables, $morphAlias)) {
            data_set(static::$variables, $morphAlias, []);
        }

        $variablePath = implode('.', array_filter([$morphAlias, $path]));
        data_set(
            static::$variables,
            $variablePath,
            array_merge(Arr::wrap(data_get(static::$variables, $variablePath) ?? []), $values)
        );
    }

    /**
     * @param  class-string<EditorButton>  $buttonClass
     */
    public static function registerButton(string $buttonClass): void
    {
        if (! is_subclass_of($buttonClass, EditorButton::class)) {
            throw new InvalidArgumentException(
                'Button class must implement ' . EditorButton::class
            );
        }

        if (! in_array($buttonClass, static::$buttons, true)) {
            static::$buttons[] = $buttonClass;
        }
    }

    /**
     * @param  array<class-string<EditorButton>>  $buttonClasses
     */
    public static function registerButtons(array $buttonClasses): void
    {
        foreach ($buttonClasses as $buttonClass) {
            static::registerButton($buttonClass);
        }
    }

    public static function registerVariable(string $key, string|array $value, ?string $modelClass = null): void
    {
        static::registerVariables([$key => $value], $modelClass);
    }

    public static function registerVariables(array $variables, ?string $modelClass = null): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        data_set(
            static::$variables,
            $morphAlias,
            array_merge(data_get(static::$variables, $morphAlias) ?? [], $variables)
        );
    }

    /**
     * @param  class-string<EditorButton>  $buttonClass
     */
    public static function removeButton(string $buttonClass): void
    {
        static::$buttons = array_values(
            array_filter(
                static::$buttons,
                fn ($class) => $class !== $buttonClass
            )
        );
    }

    public static function removeVariable(?string $modelClass = null, ?string $path = null): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        if (! array_key_exists($morphAlias, static::$variables)) {
            return;
        }

        data_forget(static::$variables, implode('.', array_filter([$morphAlias, $path])));
    }

    public static function setVariable(string|array $value, ?string $modelClass = null, ?string $path = null): void
    {
        data_set(
            static::$variables,
            implode('.', array_filter([static::getMorphAlias($modelClass), $path])),
            $value
        );
    }

    protected static function getMorphAlias(?string $modelClass): string
    {
        return is_null($modelClass)
            ? '__global__'
            : morph_alias($modelClass);
    }

    protected static function translate(array $variables): array
    {
        return Arr::mapWithKeys(
            $variables,
            fn (string $value, string $key) => [
                $key => [
                    'label' => __($key),
                    'value' => $value,
                ],
            ]
        );
    }
}
