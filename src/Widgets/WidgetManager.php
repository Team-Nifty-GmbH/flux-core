<?php

namespace FluxErp\Widgets;

use FluxErp\Traits\Widgetable;
use Illuminate\Support\Traits\Macroable;
use Livewire\Component;
use Livewire\Mechanisms\ComponentRegistry;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class WidgetManager
{
    use Macroable;

    protected $widgets = [];

    /**
     * @throws \Exception
     */
    public function register(string $name, string $widget): void
    {
        $componentRegistry = app(ComponentRegistry::class);
        $componentClass = $componentRegistry->getClass($widget);

        if (! class_exists($componentClass)) {
            throw new \Exception("The provided widget class '{$componentClass}' does not exist.");
        }

        if (! is_subclass_of($componentClass, Component::class)) {
            throw new \Exception("The provided widget class '{$componentClass}' does not extend Livewire\\Component.");
        }

        $reflection = new ReflectionClass($componentClass);
        if (method_exists($componentClass, 'mount') && $reflection->getMethod('mount')->getNumberOfParameters() !== 0) {
            throw new \Exception("The provided widget class '{$componentClass}' must not have any parameters in the mount method.");
        }

        $this->widgets[$name] = [
            'name' => $widget,
            'label' => $componentClass::getLabel(),
            'class' => $componentClass,
        ];
    }

    public function unregister(string $name): void
    {
        unset($this->widgets[$name]);
    }

    public function all(): array
    {
        return $this->widgets;
    }

    public function get(string $name): ?string
    {
        return $this->widgets[$name] ?? null;
    }

    public function autoDiscoverWidgets(string $directory = null, string $namespace = null): void
    {
        $componentRegistry = app(ComponentRegistry::class);
        $namespace = $namespace ?: config('livewire.class_namespace') . '\\Widgets';
        $path = $directory ?: app_path('Livewire/Widgets');

        if (! is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

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

                    try {
                        $this->register($componentName, $componentName);
                    } catch (\Exception $e) {
                        // Don't throw exceptions on auto discovery
                    }
                }
            }
        }
    }
}
