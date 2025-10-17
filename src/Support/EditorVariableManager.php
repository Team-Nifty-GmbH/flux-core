<?php

namespace FluxErp\Support;

use Illuminate\Support\Arr;

class EditorVariableManager
{
    protected static array $variables = [];

    public static function register(array $variables, ?string $modelClass = null): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        data_set(
            static::$variables,
            $morphAlias,
            array_merge(data_get(static::$variables, $morphAlias) ?? [], $variables)
        );
    }

    public static function registerVariable(string $key, string|array $value, ?string $modelClass = null): void
    {
        static::register([$key => $value], $modelClass);
    }

    public static function get(?string $modelClass = null, ?string $path = null): array
    {
        return data_get(
            static::$variables,
            static::getMorphAlias($modelClass) . (is_null($path) ? '' : '.' . $path),
            []
        );
    }

    public static function getWithGlobals(?string $modelClass = null, ?string $path = null): mixed
    {
        $globals = static::get();
        $merged = $modelClass
            ? array_merge(static::get($modelClass), $globals)
            : $globals;

        if (is_null($path)) {
            return $merged;
        }

        return data_get($merged, $path);
    }

    public static function getTranslated(?string $modelClass = null, ?string $path = null): mixed
    {
        $variables = static::get($modelClass, $path);

        return static::translate($variables);
    }

    public static function getTranslatedWithGlobals(?string $modelClass = null, ?string $path = null): mixed
    {
        $variables = static::getWithGlobals($modelClass, $path);

        return static::translate($variables);
    }

    public static function set(string|array $value, ?string $modelClass = null, ?string $path = null): void
    {
        data_set(
            static::$variables,
            implode('.', array_filter([static::getMorphAlias($modelClass), $path])),
            $value
        );
    }

    public static function add(string|array $value, ?string $modelClass = null, ?string $path = null): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        if (! data_get(static::$variables, $morphAlias)) {
            data_set(static::$variables, $morphAlias, []);
        }

        if (is_null($path)) {
            $current = data_get(static::$variables, $morphAlias) ?? [];
            $current[] = $value;
            data_set(static::$variables, $morphAlias, $current);
        } else {
            $current = Arr::wrap(data_get(static::$variables, $morphAlias . '.' . $path) ?? []);

            $current[] = $value;
            data_set(static::$variables, $morphAlias . '.' . $path, $current);
        }
    }

    public static function remove(?string $modelClass = null, ?string $path = null): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        if (! array_key_exists($morphAlias, static::$variables)) {
            return;
        }

        if (is_null($path)) {
            data_forget(static::$variables, $morphAlias);
        } else {
            data_forget(static::$variables, $morphAlias . '.' . $path);
        }
    }

    public static function merge(array $values, ?string $modelClass = null, ?string $path = null): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        if (! data_get(static::$variables, $morphAlias)) {
            data_set(static::$variables, $morphAlias, []);
        }

        if (is_null($path)) {
            data_set(
                static::$variables,
                $morphAlias,
                array_merge(data_get(static::$variables, $morphAlias) ?? [], $values)
            );
        } else {
            data_set(
                static::$variables,
                $morphAlias . '.' . $path,
                array_merge(Arr::wrap(data_get(static::$variables, $morphAlias . '.' . $path) ?? []), $values)
            );
        }
    }

    public static function all(): array
    {
        return static::$variables;
    }

    public static function clear(): void
    {
        static::$variables = [];
    }

    protected static function getMorphAlias(?string $modelClass): string
    {
        return is_null($modelClass)
            ? '__global__'
            : morph_alias($modelClass);
    }

    protected static function translate(array $variables): array
    {
        $translated = [];
        foreach ($variables as $key => $value) {
            $translated[$key] = [
                'label' => __($key),
                'value' => $value,
            ];
        }

        return $translated;
    }
}
