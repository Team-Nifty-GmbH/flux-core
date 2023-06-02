<?php

namespace FluxErp\Widgets;

use FluxErp\Contracts\UserWidget;
use Illuminate\Support\Traits\Macroable;
use Livewire\Component;
use Livewire\Livewire;
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
        $componentClass = Livewire::getClass($widget);

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

    public function all(): array
    {
        return $this->widgets;
    }

    public function get(string $name): ?string
    {
        return $this->widgets[$name] ?? null;
    }

    public function autoDiscoverWidgets(?string $directory = null, ?string $namespace = null): void
    {
        $namespace = $namespace ?: 'App\\Http\\Livewire\\Widgets';
        $path = $directory ?: app_path('Http/Livewire/Widgets');

        foreach (glob("{$path}/*.php") as $file) {
            $class = $namespace . '\\' . pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($class) || ! in_array(UserWidget::class, class_implements($class))) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            // Check if the class is a valid Livewire component
            if ($reflection->isSubclassOf(Component::class) && ! $reflection->isAbstract()) {
                if (class_exists($class)
                    && str_starts_with($reflection->getNamespaceName(), config('livewire.class_namespace'))
                ) {
                    $componentName = $class::getName();
                } else {
                    $componentName = Livewire::getAlias($class, $class);
                }

                try {
                    $this->register($componentName, $componentName);
                } catch (\Exception $e) {
                    // dont throw exceptions on auto discovery
                }
            }
        }
    }
}
