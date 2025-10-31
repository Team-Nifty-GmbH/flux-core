<?php

namespace FluxErp\Facades;

use FluxErp\Support\EditorVariableManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void add(string|array $value, ?string $modelClass = null, ?string $path = null)
 * @method static array all()
 * @method static void clear()
 * @method static string|array get(?string $modelClass = null, ?string $path = null, bool $withGlobals = true)
 * @method static array getTranslated(?string $modelClass = null, ?string $path = null, bool $withGlobals = true)
 * @method static void merge(array $values, ?string $modelClass = null, ?string $path = null)
 * @method static void register(array $variables, ?string $modelClass = null)
 * @method static void registerVariable(string $key, string|array $value, ?string $modelClass = null)
 * @method static void remove(?string $modelClass = null, ?string $path = null)
 * @method static void set(string|array $value, ?string $modelClass = null, ?string $path = null)
 *
 * @see EditorVariableManager
 */
class EditorVariable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EditorVariableManager::class;
    }
}
