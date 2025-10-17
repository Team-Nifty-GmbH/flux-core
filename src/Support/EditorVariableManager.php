<?php

namespace FluxErp\Support;

class EditorVariableManager
{
    protected static array $variables = [];

    public static function register(?string $modelClass, array $variables): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        data_set(
            static::$variables,
            $morphAlias,
            array_merge(data_get(static::$variables, $morphAlias, []), $variables)
        );
    }

    public static function registerVariable(?string $modelClass, string $label, string $value): void
    {
        static::register($modelClass, [$label => $value]);
    }

    public static function get(?string $modelClass, ?string $path = null): array
    {
        return data_get(
            static::$variables,
            static::getMorphAlias($modelClass) . (is_null($path) ? '' : '.' . $path),
            []
        );
    }

    public static function getWithGlobals(?string $modelClass, ?string $path = null): mixed
    {
        $merged = array_merge(static::get($modelClass), static::get(null));

        if (is_null($path)) {
            return $merged;
        }

        return data_get($merged, $path);
    }

    public static function getTranslated(?string $modelClass, ?string $path = null): mixed
    {
        $variables = static::get($modelClass, $path);

        if (! is_null($path)) {
            return $variables;
        }

        return static::translate($variables);
    }

    public static function getTranslatedWithGlobals(?string $modelClass, ?string $path = null): mixed
    {
        $merged = array_merge(static::get($modelClass), static::get(null));

        if (! is_null($path)) {
            return data_get($merged, $path);
        }

        return static::translate($merged);
    }

    public static function set(?string $modelClass, string $path, mixed $value): void
    {
        data_set(static::$variables, static::getMorphAlias($modelClass) . '.' . $path, $value);
    }

    public static function add(?string $modelClass, ?string $path, mixed $value): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        if (! data_get(static::$variables, $morphAlias)) {
            data_set(static::$variables, $morphAlias, []);
        }

        if (is_null($path)) {
            $current = data_get(static::$variables, $morphAlias, []);
            $current[] = $value;
            data_set(static::$variables, $morphAlias, $current);
        } else {
            $current = data_get(static::$variables, $morphAlias . '.' . $path, []);

            if (! is_array($current)) {
                $current = [];
            }

            $current[] = $value;
            data_set(static::$variables, $morphAlias . '.' . $path, $current);
        }
    }

    public static function remove(?string $modelClass, ?string $path = null): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        if (! data_get(static::$variables, $morphAlias)) {
            return;
        }

        if (is_null($path)) {
            data_forget(static::$variables, $morphAlias);
        } else {
            data_forget(static::$variables, $morphAlias . '.' . $path);
        }
    }

    public static function merge(?string $modelClass, ?string $path, array $values): void
    {
        $morphAlias = static::getMorphAlias($modelClass);

        if (! data_get(static::$variables, $morphAlias)) {
            data_set(static::$variables, $morphAlias, []);
        }

        if (is_null($path)) {
            data_set(
                static::$variables,
                $morphAlias,
                array_merge(data_get(static::$variables, $morphAlias, []), $values)
            );
        } else {
            $current = data_get(static::$variables, $morphAlias . '.' . $path, []);

            if (! is_array($current)) {
                $current = [];
            }

            data_set(static::$variables, $morphAlias . '.' . $path, array_merge($current, $values));
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
            : (! class_exists($modelClass)
                ? $modelClass
                : morph_alias($modelClass));
    }

    protected static function translate(array $variables): array
    {
        $translated = [];
        foreach ($variables as $key => $value) {
            $translated[__($key)] = $value;
        }

        return $translated;
    }
}
