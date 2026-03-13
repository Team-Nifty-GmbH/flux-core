<?php

namespace FluxErp\Support\Editor;

use FluxErp\Contracts\EditorButton;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
        $current[] = static::wrapValue($value);

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

    protected static function wrapValue(string|array $value, ?string $key = null, ?string $morphAlias = null, ?string $path = null): array
    {
        if (is_array($value) && array_key_exists('id', $value)) {
            return $value;
        }

        $expression = is_string($value) ? $value : ($value['expression'] ?? null);

        $id = $key !== null && $morphAlias !== null
            ? implode('.', array_filter([$morphAlias, $path, Str::snake($key)]))
            : null;

        return ['id' => $id, 'expression' => $expression];
    }

    protected static function isVariableEntry(mixed $value): bool
    {
        return is_array($value) && ! is_null($value['id'] ?? null);
    }

    /**
     * @return array<class-string<EditorButton>>
     */
    public static function getButtons(): array
    {
        return static::$buttons;
    }

    public static function getVariables(?string $modelClass = null, ?string $path = null, bool $withGlobals = true): array
    {
        $morphAlias = static::getMorphAlias($modelClass);

        // Get variables at the requested path
        $data = data_get(
            static::$variables,
            implode('.', array_filter([$morphAlias, $path])),
        ) ?? [];

        // Filter out nested paths (arrays) to only return flat key-value pairs
        if (is_array($data)) {
            $data = array_filter($data, static::isVariableEntry(...));
        }

        // When a path is specified, also include parent level variables
        if ($path && $modelClass) {
            $parentData = data_get(static::$variables, $morphAlias) ?? [];
            if (is_array($parentData)) {
                $parentData = array_filter($parentData, static::isVariableEntry(...));
            }

            $data = array_merge($parentData, $data);
        }

        if (! $withGlobals || is_null($modelClass)) {
            return $data;
        }

        $globals = data_get(
            static::$variables,
            static::getMorphAlias(null),
        ) ?? [];

        // Filter out nested paths from globals as well
        if (is_array($globals)) {
            $globals = array_filter($globals, static::isVariableEntry(...));
        }

        return array_merge($data, $globals);
    }

    public static function getTranslatedVariables(
        ?string $modelClass = null,
        ?string $path = null,
        bool $withGlobals = true
    ): array {
        return static::translate(static::getVariables($modelClass, $path, $withGlobals));
    }

    public static function resolveById(?string $id): ?string
    {
        if (is_null($id)) {
            return null;
        }

        foreach (static::$variables as $entries) {
            $result = static::findExpressionById($id, $entries);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
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

        $wrapped = [];
        foreach ($values as $key => $value) {
            $wrapped[$key] = static::wrapValue($value, $key, $morphAlias, $path);
        }

        $variablePath = implode('.', array_filter([$morphAlias, $path]));
        data_set(
            static::$variables,
            $variablePath,
            array_merge(Arr::wrap(data_get(static::$variables, $variablePath) ?? []), $wrapped)
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

        $wrapped = [];
        foreach ($variables as $key => $value) {
            $wrapped[$key] = static::wrapValue($value, $key, $morphAlias);
        }

        data_set(
            static::$variables,
            $morphAlias,
            array_merge(data_get(static::$variables, $morphAlias) ?? [], $wrapped)
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
            static::wrapValue($value)
        );
    }

    protected static function findExpressionById(string $id, mixed $entries): ?string
    {
        if (! is_array($entries)) {
            return null;
        }

        // Check if this is a leaf entry with id/expression
        if (array_key_exists('id', $entries) && $entries['id'] === $id) {
            return $entries['expression'] ?? null;
        }

        // Recurse into nested entries
        foreach ($entries as $entry) {
            $result = static::findExpressionById($id, $entry);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
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
            fn (array $entry, string $key) => [
                $key => [
                    'label' => __($key),
                    'value' => $entry['id'],
                ],
            ]
        );
    }
}
