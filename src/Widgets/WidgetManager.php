<?php

namespace FluxErp\Widgets;

use Exception;
use FluxErp\Traits\Widgetable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Traits\Macroable;
use Livewire\Component;
use Livewire\Mechanisms\ComponentRegistry;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Throwable;

class WidgetManager
{
    use Macroable;

    protected $widgets = [];

    public function all(): array
    {
        return $this->widgets;
    }

    public function autoDiscoverWidgets(?string $directory = null, ?string $namespace = null): void
    {
        $componentRegistry = app(ComponentRegistry::class);
        $namespace = $namespace ?: config('livewire.class_namespace') . '\\Widgets';
        $path = $directory ?: app_path('Livewire/Widgets');

        if (! is_dir($path)) {
            return;
        }

        $cacheKey = md5($path . $namespace);

        try {
            $widgets = Cache::get('flux.widgets.' . $cacheKey);
        } catch (Throwable) {
            $widgets = null;
        }

        if (! is_null($widgets) && ! app()->runningInConsole()) {
            $iterator = [];
        } else {
            $widgets = [];
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
        }

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = ltrim(str_replace($path, '', $file->getPath()), DIRECTORY_SEPARATOR);
                $subNameSpace = ! empty($relativePath)
                    ? str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath) . '\\'
                    : '';
                $class = $namespace . '\\' . $subNameSpace . $file->getBasename('.php');

                if (! class_exists($class) || ! in_array(Widgetable::class, class_uses_recursive($class))) {
                    continue;
                }

                $reflection = new ReflectionClass($class);

                // Check if the class is a valid Livewire component
                if ($reflection->isSubclassOf(Component::class) && ! $reflection->isAbstract()) {
                    if (class_exists($class) && str_starts_with($reflection->getNamespaceName(), $namespace)) {
                        $componentName = $componentRegistry->getName($class);
                    } else {
                        continue;
                    }

                    $widgets[$componentName] = $class;
                }
            }
        }

        foreach ($widgets as $name => $class) {
            try {
                $this->register($name, $name);
            } catch (Exception $e) {
                // Don't throw exceptions on auto discovery
            }
        }

        try {
            Cache::put('flux.widgets.' . $cacheKey, $widgets);
        } catch (Throwable) {
            // Ignore exceptions during cache put
        }
    }

    public function get(string $name): ?array
    {
        return collect($this->widgets)
            ->when(
                class_exists($name),
                fn (Collection $widgets) => $widgets->firstWhere('class', $name),
                fn (Collection $widgets) => $widgets->get($name)
            ) ?: null;
    }

    /**
     * @throws Exception
     */
    public function register(string $name, string $widget): void
    {
        $componentRegistry = app(ComponentRegistry::class);
        $componentClass = $componentRegistry->getClass($widget);

        if (! class_exists($componentClass)) {
            throw new Exception("The provided widget class '{$componentClass}' does not exist.");
        }

        if (! is_subclass_of($componentClass, Component::class)) {
            throw new Exception(
                "The provided widget class '{$componentClass}' does not extend Livewire\\Component."
            );
        }

        $reflection = new ReflectionClass($componentClass);
        if (method_exists($componentClass, 'mount')
            && $reflection->getMethod('mount')->getNumberOfParameters() !== 0
        ) {
            throw new Exception(
                "The provided widget class '{$componentClass}' must not have any parameters in the mount method."
            );
        }

        if (! in_array(Widgetable::class, class_uses_recursive($componentClass))) {
            throw new Exception(
                "The provided widget class '{$componentClass}' does not use the Widgetable trait."
            );
        }

        $this->widgets[$name] = [
            'component_name' => $widget,
            'dashboard_component' => $componentClass::dashboardComponent(),
            'label' => $componentClass::getLabel(),
            'class' => $componentClass,
            'defaultWidth' => method_exists($componentClass, 'getDefaultWidth')
                ? $componentClass::getDefaultWidth()
                : 1,
            'defaultHeight' => method_exists($componentClass, 'getDefaultHeight')
                ? $componentClass::getDefaultHeight()
                : 1,
            'defaultOrderRow' => method_exists($componentClass, 'getDefaultOrderRow')
                ? $componentClass::getDefaultOrderRow()
                : 0,
            'defaultOrderColumn' => method_exists($componentClass, 'getDefaultOrderColumn')
                ? $componentClass::getDefaultOrderColumn()
                : 0,
        ];
    }

    public function unregister(string $name): void
    {
        unset($this->widgets[$name]);
    }
}
