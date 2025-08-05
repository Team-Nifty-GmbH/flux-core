<?php

namespace FluxErp\Providers;

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
use FluxErp\Support\MediaLibrary\UrlGenerator;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class BindingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides(): array
    {
        return [
            Registry::class,
            'datatype.registry',
            DefaultUrlGenerator::class,
            StatefulGuard::class,
        ];
    }

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
    }
}
