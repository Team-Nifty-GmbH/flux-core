<?php

namespace FluxErp;

use FluxErp\Actions\ActionManager;
use FluxErp\Assets\AssetManager;
use FluxErp\Console\Commands\Init\InitEnv;
use FluxErp\Console\Commands\Init\InitPermissions;
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
use FluxErp\Facades\Action;
use FluxErp\Facades\Menu;
use FluxErp\Facades\Repeatable;
use FluxErp\Facades\Widget;
use FluxErp\Factories\ValidatorFactory;
use FluxErp\Helpers\Composer;
use FluxErp\Helpers\MediaLibraryDownloader;
use FluxErp\Http\Middleware\Localization;
use FluxErp\Http\Middleware\Permissions;
use FluxErp\Menu\MenuManager;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Client;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\Project;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Widgets\WidgetManager;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Laravel\Scout\Builder;
use Livewire\Livewire;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\Translatable\Facades\Translatable;

class FluxServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->offerPublishing();
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'flux');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
        $this->registerBladeComponents();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'flux');
        $this->registerLivewireComponents();
        $this->registerMiddleware();
        $this->registerConfig();
        $this->registerMarcos();

        $this->app->extend('validator', function () {
            return $this->app->get(ValidatorFactory::class);
        });

        $this->app->extend('composer', function () {
            return $this->app->get(Composer::class);
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

        Translatable::fallback(
            fallbackAny: true,
        );

        $this->app->alias(Registry::class, 'datatype.registry');

        $this->app->singleton('flux.asset_manager', fn ($app) => app(AssetManager::class));
        $this->app->singleton('flux.widget_manager', fn ($app) => app(WidgetManager::class));
        $this->app->singleton('flux.action_manager', fn ($app) => app(ActionManager::class));
        $this->app->singleton('flux.menu_manager', fn ($app) => app(MenuManager::class));
        $this->app->singleton('flux.repeatable_manager', fn ($app) => app(RepeatableManager::class));

        $this->app->extend(Builder::class, function (Builder $scoutBuilder) {
            if (($user = auth()->user()) instanceof User
                && in_array(HasClientAssignment::class, class_uses_recursive($scoutBuilder->model))
                && $scoutBuilder->model->isRelation('client')
                && ($relation = $scoutBuilder->model->client()) instanceof BelongsTo
            ) {
                $clients = $user->clients()->pluck('id')->toArray() ?: Client::query()->pluck('id')->toArray();

                $scoutBuilder->whereIn($relation->getForeignKeyName(), $clients);
            }

            return $scoutBuilder;
        });
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

        Widget::autoDiscoverWidgets(flux_path('src/Livewire/Widgets'), 'FluxErp\Livewire\Widgets');
        Widget::autoDiscoverWidgets();

        Action::autoDiscover(flux_path('src/Actions'), 'FluxErp\Actions');
        Action::autoDiscover();

        // Register repeatable artisan commands
        Repeatable::autoDiscover(flux_path('src/Console/Commands'), 'FluxErp\Console\Commands');
        // Register repeatable jobs
        Repeatable::autoDiscover(flux_path('src/Jobs'), 'FluxErp\Jobs');
        // Register repeatable invokable classes in "Repeatable" directory
        Repeatable::autoDiscover(flux_path('src/Repeatable'), 'FluxErp\Repeatable');
        // Register repeatable artisan commands, jobs and invokable classes (in "Repeatable" directory) from app
        Repeatable::autoDiscover();
    }

    protected function registerMarcos(): void
    {
        if (! Str::hasMacro('iban')) {
            Str::macro('iban', function (?string $iban) {
                return trim(chunk_split($iban ?? '', 4, ' '));
            });
        }

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
                function (int $perPage = 25, ?int $page = null, array $options = [], ?string $urlParams = null) {
                    $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                    return (new LengthAwarePaginator(
                        $this->forPage($page, $perPage), $this->count(), $perPage, $page, $options))
                        ->withPath($urlParams ? dirname(url()->full()) . $urlParams : url()->full());
                });
        }

        \Illuminate\Routing\Route::macro('registersMenuItem',
            function (?string $label = null, ?string $icon = null, ?int $order = null) {
                Menu::register(
                    route: $this,
                    label: $label,
                    icon: $icon,
                    order: $order,
                );
            }
        );

        \Illuminate\Routing\Route::macro('getPermissionName',
            function () {
                $methods = array_flip($this->methods());
                Arr::forget($methods, 'HEAD');
                $method = array_keys($methods)[0];

                $uri = array_flip(array_filter(explode('/', $this->uri)));
                if (! $uri) {
                    return null;
                }

                $uri = array_keys($uri);
                $uri[] = $method;

                return strtolower(implode('.', $uri));
            }
        );

        \Illuminate\Routing\Route::macro('hasPermission', function () {
            $this->setAction(array_merge($this->getAction(), [
                'permission' => route_to_permission($this, false),
            ]));

            return $this;
        });
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
        $this->mergeConfigFrom(__DIR__ . '/../config/fortify.php', 'fortify');
        $this->mergeConfigFrom(__DIR__ . '/../config/notifications.php', 'notifications');
        $this->mergeConfigFrom(__DIR__ . '/../config/scout.php', 'scout');
        config(['auth' => require __DIR__ . '/../config/auth.php']);
        config(['activitylog' => require __DIR__ . '/../config/activitylog.php']);
        config(['logging' => array_merge_recursive(config('logging'), require __DIR__ . '/../config/logging.php')]);
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
                Address::class => [
                    'filterableAttributes' => [
                        'is_main_address',
                        'contact_id',
                    ],
                    'sortableAttributes' => ['*'],
                ],
                Category::class => [
                    'filterableAttributes' => [
                        'model_type',
                    ],
                ],
                LedgerAccount::class => [
                    'filterableAttributes' => [
                        'ledger_account_type_enum',
                        'is_automatic',
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
                Permission::class => [
                    'filterableAttributes' => [
                        'guard_name',
                    ],
                    'sortableAttributes' => [
                        'name',
                    ],
                ],
                Product::class => [
                    'filterableAttributes' => [
                        'is_active',
                        'parent_id',
                    ],
                    'sortableAttributes' => ['*'],
                ],
                Project::class => [
                    'filterableAttributes' => [
                        'parent_id',
                        'state',
                    ],
                    'sortableAttributes' => ['*'],
                ],
                SerialNumber::class => [
                    'filterableAttributes' => [
                        'address_id',
                    ],
                ],
                Task::class => [
                    'filterableAttributes' => [
                        'project_id',
                        'state',
                    ],
                    'sortableAttributes' => ['*'],
                ],
                Ticket::class => [
                    'filterableAttributes' => [
                        'authenticatable_type',
                        'authenticatable_id',
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
                    'FluxErp\\Actions\\',
                ],
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
        $livewireNamespace = 'FluxErp\\Livewire\\';

        foreach ($this->getViewClassAliasFromNamespace($livewireNamespace) as $alias => $class) {
            Livewire::component($alias, $class);
        }
    }

    private function getViewClassAliasFromNamespace(string $namespace, ?string $directoryPath = null): array
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
            // commands required for installation
            $this->commands(InitEnv::class);
            $this->commands(InitPermissions::class);

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
        /** @var Kernel $kernel */
        $kernel = app()->make(Kernel::class);
        $kernel->prependMiddlewareToGroup('api', EnsureFrontendRequestsAreStateful::class);

        $kernel->appendMiddlewareToGroup('web', Localization::class);

        $this->app['router']->aliasMiddleware('abilities', CheckAbilities::class);
        $this->app['router']->aliasMiddleware('role', RoleMiddleware::class);
        $this->app['router']->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
        $this->app['router']->aliasMiddleware('permission', Permissions::class);
        $this->app['router']->aliasMiddleware('localization', Localization::class);
    }
}
