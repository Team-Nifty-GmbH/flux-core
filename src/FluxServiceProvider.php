<?php

namespace FluxErp;

use FluxErp\Actions\ActionManager;
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
use FluxErp\Facades\Action;
use FluxErp\Facades\Widget;
use FluxErp\Factories\ValidatorFactory;
use FluxErp\Helpers\MediaLibraryDownloader;
use FluxErp\Http\Middleware\Localization;
use FluxErp\Http\Middleware\Permissions;
use FluxErp\Logging\DatabaseCustomLogger;
use FluxErp\Logging\DatabaseLoggingHandler;
use FluxErp\Models\Address;
use FluxErp\Models\Order;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\ProjectTask;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Ticket;
use FluxErp\Models\Token;
use FluxErp\Models\User;
use FluxErp\Widgets\WidgetManager;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Livewire\Livewire;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;

class FluxServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->offerPublishing();
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../lang', 'flux');
            $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'flux');

        $this->registerBladeComponents();
        $this->registerLivewireComponents();
        $this->registerMiddleware();
        $this->registerconfig();
        $this->registerMarcos();

        $this->app->extend('validator', function () {
            return $this->app->get(ValidatorFactory::class);
        });

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

        $this->app->singleton('flux.widget_manager', fn ($app) => new WidgetManager());

        $this->app->singleton('flux.action_manager', fn ($app) => new ActionManager());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        bcscale(9);

        $this->registerCommands();

        if (! Response::hasMacro('attachment')) {
            Response::macro('attachment', function ($content, $filename = 'download.pdf') {
                $headers = [
                    'Content-type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                return Response::make($content, 200, $headers);
            });
        }

        Widget::autoDiscoverWidgets(flux_path('src/Http/Livewire/Widgets'), 'FluxErp\Http\Livewire\Widgets');
        Widget::autoDiscoverWidgets();

        Action::autoDiscover(flux_path('src/Actions'), 'FluxErp\Actions');
        Action::autoDiscover();
    }

    protected function registerMarcos(): void
    {
        if (! Request::hasMacro('isPortal')) {
            Request::macro('isPortal', function () {
                return $this->getHost() === preg_replace(
                    '(^https?://)',
                    '',
                    config('flux.portal_domain')
                );
            });
        }

        if (! Collection::hasMacro('paginate')) {
            Collection::macro('paginate',
                function (int $perPage = 25, int $page = null, array $options = [], string $urlParams = null) {
                    $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                    return (new LengthAwarePaginator(
                        $this->forPage($page, $perPage), $this->count(), $perPage, $page, $options))
                        ->withPath($urlParams ? dirname(url()->full()) . $urlParams : url()->full());
                });
        }
    }

    protected function offerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'flux-migrations');
        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'flux-seeders');
        $this->publishes([
            __DIR__ . '/../config/flux.php' => config_path('flux.php'),
        ], 'flux-config');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/flux'),
        ], 'flux-views');
        $this->publishes([
            __DIR__ . '/../lang' => base_path('lang/vendor/team-nifty-gmbh/flux'),
        ], 'flux-translations');
        $this->publishes([
            __DIR__ . '/../public/build' => public_path('vendor/team-nifty-gmbh/flux'),
        ], 'flux-assets');
        $this->publishes([
            __DIR__ . '/../docker' => base_path('docker'),
        ], 'flux-docker');
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/flux.php', 'flux');
        $this->mergeConfigFrom(__DIR__ . '/../config/print.php', 'print');
        $this->mergeConfigFrom(__DIR__ . '/../config/fortify.php', 'fortify');
        $this->mergeConfigFrom(__DIR__ . '/../config/notifications.php', 'notifications');
        $this->mergeConfigFrom(__DIR__ . '/../config/scout.php', 'scout');
        config(['auth' => require __DIR__ . '/../config/auth.php']);
        config(['activitylog' => require __DIR__ . '/../config/activitylog.php']);
        config(['logging' => require __DIR__ . '/../config/logging.php']);
        config(['wireui.heroicons.alias' => 'heroicons']);
        config(['wireui.modal' => [
            'zIndex' => env('WIREUI_MODAL_Z_INDEX', 'z-20'),
            'maxWidth' => env('WIREUI_MODAL_MAX_WIDTH', '2xl'),
            'spacing' => env('WIREUI_MODAL_SPACING', 'p-4'),
            'align' => env('WIREUI_MODAL_ALIGN', 'start'),
            'blur' => env('WIREUI_MODAL_BLUR', false),
        ]]);
        config(['media-library.media_downloader' => MediaLibraryDownloader::class]);
        config([
            'scout.meilisearch.index-settings' => [
                SerialNumber::class => [
                    'filterableAttributes' => [
                        'address_id',
                    ],
                ],
                Permission::class => [
                    'filterableAttributes' => [
                        'guard_name',
                    ],
                    'sortableAttributes' => [
                        'name',
                    ],
                ],
                Ticket::class => [
                    'filterableAttributes' => [
                        'authenticatable_type',
                        'authenticatable_id',
                        'state',
                    ],
                    'sortableAttributes' => ['*'],
                ],
                Address::class => [
                    'filterableAttributes' => [
                        'is_main_address',
                        'contact_id',
                    ],
                    'sortableAttributes' => ['*'],
                ],
                Order::class => [
                    'filterableAttributes' => [
                        'parent_id',
                        'contact_id',
                        'is_locked',
                    ],
                    'sortableAttributes' => ['*'],
                ],
                Product::class => [
                    'filterableAttributes' => [
                        'is_active',
                        'parent_id',
                    ],
                    'sortableAttributes' => ['*'],
                ],
                ProjectTask::class => [
                    'filterableAttributes' => [
                        'project_id',
                        'state',
                    ],
                    'sortableAttributes' => ['*'],
                ],
                User::class => [
                    'filterableAttributes' => [
                        'is_active',
                    ],
                ],
            ],
        ]);

        if (app()->runningInConsole()) {
            config([
                'tinker.alias' => [
                    'FluxErp\\Models\\',
                    'FluxErp\\Services\\',
                    'FluxErp\\Actions\\',
                ]
            ]);
        }
    }

    protected function registerBladeComponents(): void
    {
        $directoryIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__ . '/../resources/views/components')
        );
        $phpFiles = new RegexIterator($directoryIterator, '/\.blade\.php$/');

        foreach ($phpFiles as $phpFile) {
            $relativePath = Str::replace(__DIR__ . '/../resources/views/components/', '', $phpFile->getRealPath());
            $relativePath = Str::replace(DIRECTORY_SEPARATOR, '.', Str::remove('.blade.php', $relativePath));
            $relativePath = Str::afterLast($relativePath, 'views.components.');
            Blade::component('flux::components.' . $relativePath, Str::remove('.index', $relativePath));
        }

        foreach ($this->getViewClassAliasFromNamespace('FluxErp\\View\\Components') as $alias => $class) {
            Blade::component($class, $alias);
        }
    }

    protected function registerLivewireComponents(): void
    {
        $livewireNamespace = 'FluxErp\\Http\\Livewire\\';
        $manifest = app(\Livewire\LivewireComponentsFinder::class)->getManifest();

        foreach ($this->getViewClassAliasFromNamespace($livewireNamespace) as $alias => $class) {
            // if an alias is already registered, skip it
            if ($manifest[$alias] ?? false) {
                continue;
            }

            Livewire::component($alias, $class);
        }
    }

    private function getViewClassAliasFromNamespace(string $namespace, string $directoryPath = null): array
    {
        $directoryPath = $directoryPath ?: Str::replace(['\\', 'FluxErp'], ['/', __DIR__], $namespace);
        $directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryPath));
        $phpFiles = new RegexIterator($directoryIterator, '/\.php$/');
        $components = [];

        foreach ($phpFiles as $phpFile) {
            $relativePath = Str::replace($directoryPath, '', $phpFile->getRealPath());
            $relativePath = Str::replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
            $class = $namespace . str_replace(
                '/',
                '\\',
                pathinfo($relativePath, PATHINFO_FILENAME)
            );

            if (class_exists($class)) {
                $exploded = explode('\\', $relativePath);
                array_walk($exploded, function (&$value) {
                    $value = Str::snake(Str::remove('.php', $value), '-');
                });

                $alias = ltrim(implode('.', $exploded), '.');
                $components[$alias] = $class;
            }
        }

        return $components;
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__ . '/Console/Commands')
        );
        $commandClasses = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $classPath = str_replace([__DIR__ . '/', '/'], ['', '\\'], $file->getPathname());
                $classNamespace = '\\FluxErp\\';
                $class = $classNamespace . str_replace('.php', '', $classPath);
                $commandClasses[] = $class;
            }
        }

        $this->commands($commandClasses);
    }

    private function registerMiddleware(): void
    {
        $kernel = app()->make(Kernel::class);
        $kernel->prependMiddlewareToGroup('api', EnsureFrontendRequestsAreStateful::class);
        $kernel->appendMiddlewareToGroup('api', Permissions::class);

        $kernel->appendMiddlewareToGroup('web', Permissions::class);
        $kernel->appendMiddlewareToGroup('web', Localization::class);

        $this->app['router']->aliasMiddleware('abilities', CheckAbilities::class);
        $this->app['router']->aliasMiddleware('role', RoleMiddleware::class);
        $this->app['router']->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
        $this->app['router']->aliasMiddleware('permission', Permissions::class);
        $this->app['router']->aliasMiddleware('localization', Localization::class);
    }
}
