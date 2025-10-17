<?php

namespace FluxErp\Facades;

use FluxErp\Support\EditorVariableManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(?string $modelClass, array $variables)
 * @method static void registerVariable(?string $modelClass, string $label, string $value)
 * @method static mixed get(?string $modelClass, ?string $path = null)
 * @method static mixed getWithGlobals(?string $modelClass, ?string $path = null)
 * @method static mixed getTranslated(?string $modelClass, ?string $path = null)
 * @method static mixed getTranslatedWithGlobals(?string $modelClass, ?string $path = null)
 * @method static void set(?string $modelClass, string $path, mixed $value)
 * @method static void add(?string $modelClass, ?string $path, mixed $value)
 * @method static void remove(?string $modelClass, ?string $path = null)
 * @method static void merge(?string $modelClass, ?string $path, array $values)
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
