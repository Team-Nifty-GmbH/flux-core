<?php

namespace FluxErp\Facades;

use FluxErp\Support\EditorVariableManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(array $variables, ?string $modelClass = null)
 * @method static void registerVariable(string $label, string $value, ?string $modelClass = null)
 * @method static mixed get(?string $modelClass = null, ?string $path = null)
 * @method static mixed getWithGlobals(?string $modelClass = null, ?string $path = null)
 * @method static mixed getTranslated(?string $modelClass = null, ?string $path = null)
 * @method static mixed getTranslatedWithGlobals(?string $modelClass = null, ?string $path = null)
 * @method static void set(string $value, ?string $modelClass = null, ?string $path = null)
 * @method static void add(string $value, ?string $modelClass = null, ?string $path = null)
 * @method static void remove(?string $modelClass = null, ?string $path = null)
 * @method static void merge(array $values, ?string $modelClass = null, ?string $path = null)
 * @method static array all()
 * @method static void clear()
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
