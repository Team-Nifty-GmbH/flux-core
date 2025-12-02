<?php

namespace FluxErp\Facades;

use FluxErp\Support\EditorManager;
use Illuminate\Support\Facades\Facade;

/**
 * Variables
 *
 * @method static void addVariable(string|array $value, ?string $modelClass = null, ?string $path = null)
 * @method static array allVariables()
 * @method static void clearVariables()
 * @method static string|array getVariables(?string $modelClass = null, ?string $path = null, bool $withGlobals = true)
 * @method static array getTranslatedVariables(?string $modelClass = null, ?string $path = null, bool $withGlobals = true)
 * @method static void mergeVariables(array $values, ?string $modelClass = null, ?string $path = null)
 * @method static void registerVariables(array $variables, ?string $modelClass = null)
 * @method static void registerVariable(string $key, string|array $value, ?string $modelClass = null)
 * @method static void removeVariable(?string $modelClass = null, ?string $path = null)
 * @method static void setVariable(string|array $value, ?string $modelClass = null, ?string $path = null)
 *
 * Buttons
 * @method static void registerButton(string $buttonClass)
 * @method static void registerButtons(array $buttonClasses)
 * @method static array getButtons()
 * @method static void clearButtons()
 * @method static bool hasButton(string $buttonClass)
 * @method static void removeButton(string $buttonClass)
 * @method static bool isLivewireButton(string $buttonClass)
 *
 * @see EditorManager
 */
class Editor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EditorManager::class;
    }
}
