<?php

namespace FluxErp\Providers;

use FluxErp\Actions\ActionManager;
use FluxErp\Assets\AssetManager;
use FluxErp\Console\Scheduling\RepeatableManager;
use FluxErp\DataType\ArrayHandler;
use FluxErp\DataType\BooleanHandler;
use FluxErp\DataType\DateTimeHandler;
use FluxErp\DataType\FloatHandler;
use FluxErp\DataType\IntegerHandler;
use FluxErp\DataType\ModelCollectionHandler;
use FluxErp\DataType\NullHandler;
use FluxErp\DataType\ObjectHandler;
use FluxErp\DataType\Registry;
use FluxErp\DataType\SerializableHandler;
use FluxErp\DataType\StringHandler;
use FluxErp\Menu\MenuManager;
use FluxErp\Support\MediaLibrary\UrlGenerator;
use FluxErp\Widgets\WidgetManager;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class BindingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->bind(StatefulGuard::class, fn () => Auth::guard('web'));
        $this->app->bind(DefaultUrlGenerator::class, UrlGenerator::class);

        $this->app->singleton(Registry::class, function () {
            $registry = new Registry();
            $dataTypeHandlers = [
                BooleanHandler::class,
                NullHandler::class,
                IntegerHandler::class,
                FloatHandler::class,
                StringHandler::class,
                DateTimeHandler::class,
                ArrayHandler::class,
                ModelCollectionHandler::class,
                SerializableHandler::class,
                ObjectHandler::class,
            ];

            foreach ($dataTypeHandlers as $handler) {
                $registry->addHandler(new $handler());
            }

            return $registry;
        });

        $this->app->alias(Registry::class, 'datatype.registry');

        $this->app->singleton('flux.asset_manager', fn ($app) => app(AssetManager::class));
        $this->app->singleton('flux.widget_manager', fn ($app) => app(WidgetManager::class));
        $this->app->singleton('flux.action_manager', fn ($app) => app(ActionManager::class));
        $this->app->singleton('flux.menu_manager', fn ($app) => app(MenuManager::class));
        $this->app->singleton('flux.repeatable_manager', fn ($app) => app(RepeatableManager::class));
    }

    public function provides(): array
    {
        return [
            AssetManager::class,
            WidgetManager::class,
            ActionManager::class,
            MenuManager::class,
            RepeatableManager::class,
            'flux.asset_manager',
            'flux.widget_manager',
            'flux.action_manager',
            'flux.menu_manager',
            'flux.repeatable_manager',
            Registry::class,
            'datatype.registry',
            'composer',
            DefaultUrlGenerator::class,
            StatefulGuard::class,
        ];
    }
}
