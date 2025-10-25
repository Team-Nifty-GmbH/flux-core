<?php

namespace FluxErp\Providers;

use Exception;
use FluxErp\Widgets\WidgetManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Livewire\Mechanisms\ComponentRegistry;

class WidgetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WidgetManager::class, function (Application $app): WidgetManager {
            $manager = new WidgetManager();

            $cachePath = $app->bootstrapPath('cache/flux-widgets.php');

            if (file_exists($cachePath)) {
                $componentRegistry = $app->make(ComponentRegistry::class);
                $widgets = require $cachePath;
                foreach ($widgets as $widgetClass) {
                    $componentName = $componentRegistry->getName($widgetClass);
                    try {
                        $manager->register($componentName, $componentName);
                    } catch (Exception) {
                        // Skip widgets that fail to register
                    }
                }
            } else {
                $manager->autoDiscoverWidgets(flux_path('src/Livewire/Widgets'), 'FluxErp\Livewire\Widgets');
                $manager->autoDiscoverWidgets();
            }

            return $manager;
        });
    }
}
