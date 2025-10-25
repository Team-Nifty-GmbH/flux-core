<?php

namespace FluxErp;

use FluxErp\Assets\AssetManager;
use FluxErp\Facades\ProductType;
use FluxErp\Helpers\Composer;
use FluxErp\Helpers\Livewire\Features\SupportFormObjects;
use FluxErp\Helpers\MediaLibraryDownloader;
use FluxErp\Http\Middleware\AuthContextMiddleware;
use FluxErp\Http\Middleware\Localization;
use FluxErp\Http\Middleware\Permissions;
use FluxErp\Http\Middleware\PortalMiddleware;
use FluxErp\Http\Middleware\SetJobAuthenticatedUserMiddleware;
use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Activity;
use FluxErp\Models\Currency;
use FluxErp\Models\Notification;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Providers\ActionServiceProvider;
use FluxErp\Providers\AuthServiceProvider;
use FluxErp\Providers\BindingServiceProvider;
use FluxErp\Providers\BroadcastServiceProvider;
use FluxErp\Providers\EditorVariableServiceProvider;
use FluxErp\Providers\EventServiceProvider;
use FluxErp\Providers\MacroServiceProvider;
use FluxErp\Providers\MenuServiceProvider;
use FluxErp\Providers\MorphMapServiceProvider;
use FluxErp\Providers\RepeatableServiceProvider;
use FluxErp\Providers\SanctumServiceProvider;
use FluxErp\Providers\TestServiceProvider;
use FluxErp\Providers\ViewServiceProvider;
use FluxErp\Providers\WidgetServiceProvider;
use FluxErp\Support\Bus\Dispatcher as FluxDispatcher;
use FluxErp\Support\Container\ProductTypeManager;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Bus\Dispatcher;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Livewire\Component;
use Livewire\Livewire;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\Translatable\Facades\Translatable;
use Throwable;

class FluxServiceProvider extends ServiceProvider
{
    public static bool $registerApiRoutes = true;

    public static bool $registerFluxRoutes = true;

    public static bool $registerPortalRoutes = true;

    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();

        $this->app->booted(function (): void {
            try {
                if ($iso = resolve_static(Currency::class, 'default')?->iso) {
                    Number::useCurrency($iso);
                }
            } catch (QueryException) {
            }
        });
        Number::useLocale(app()->getLocale());

        bcscale(9);
        $this->bootMiddleware();
        $this->bootCommands();

        if ($this->app->runningInConsole()) {
            $this->optimizes('flux:optimize', 'flux:optimize-clear');
            $this->optimizes('settings:discover', 'settings:clear-cache');
        }

        $this->bootRoutes();
        $this->registerLivewireComponents();
        $this->registerBladeComponents();

        ProductType::register(
            name: 'product',
            class: Product::class,
            default: true
        );
    }

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->offerPublishing();
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->app->bind(
            'path.lang',
            fn () => [__DIR__ . '/../lang', base_path('lang')]
        );

        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'flux');
        $this->registerConfig();
        $this->registerExtensions();

        Translatable::fallback(
            fallbackAny: true,
        );

        app('livewire')->componentHook(SupportFormObjects::class);
        $this->app->bind(DatabaseNotification::class, Notification::class);

        $this->app->singleton(AssetManager::class);
        $this->app->singleton(ProductTypeManager::class, function (): ProductTypeManager {
            return new ProductTypeManager();
        });

        // Register core providers in correct order
        $this->app->register(MorphMapServiceProvider::class);
        $this->app->register(BindingServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(SanctumServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);
        $this->app->register(BroadcastServiceProvider::class);
        $this->app->register(MacroServiceProvider::class);
        $this->app->register(ActionServiceProvider::class);
        $this->app->register(RepeatableServiceProvider::class);
        $this->app->register(WidgetServiceProvider::class);
        $this->app->register(EditorVariableServiceProvider::class);
        $this->app->register(MenuServiceProvider::class);

        if ($this->app->runningUnitTests()) {
            $this->app->register(TestServiceProvider::class);
        }
    }

    protected function bootCommands(): void
    {
        $this->commands(
            file_exists($cachePath = $this->app->bootstrapPath('cache/flux-commands.php'))
                ? require $cachePath
                : once(fn () => $this->findCommands())
        );
    }

    protected function bootRoutes(): void
    {
        if (static::$registerFluxRoutes || static::$registerPortalRoutes) {
            Authenticate::redirectUsing(fn (HttpRequest $request) => route('login', absolute: false));
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if (static::$registerApiRoutes) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        if (static::$registerPortalRoutes) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/frontend/portal.php');
        }

        if (static::$registerFluxRoutes) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/frontend/web.php');
        }

        if (static::$registerApiRoutes) {
            RateLimiter::for('api', function (HttpRequest $request) {
                return Limit::perMinute(config('flux.rate_limit'))
                    ->by(optional($request->user())->id ?: $request->ip());
            });
        }

        RouteFacade::pattern('id', '[0-9]+');
    }

    protected function findCommands(): array
    {
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

        return $commandClasses;
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

    protected function registerBladeComponents(): void
    {
        $cachePath = $this->app->bootstrapPath('cache/flux-blade-components.php');

        if (file_exists($cachePath)) {
            $components = require $cachePath;
        } else {
            $directoryIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(__DIR__ . '/../resources/views/components')
            );
            $phpFiles = new RegexIterator($directoryIterator, '/\.blade\.php$/');

            $components = [];
            foreach ($phpFiles as $phpFile) {
                $relativePath = Str::replace(__DIR__ . '/../resources/views/components/', '', $phpFile->getRealPath());
                $relativePath = Str::replace(DIRECTORY_SEPARATOR, '.', Str::remove('.blade.php', $relativePath));
                $relativePath = Str::afterLast($relativePath, 'views.components.');

                $components[] = [
                    'view' => 'flux::components.' . $relativePath,
                    'alias' => Str::remove('.index', $relativePath),
                ];
            }
        }

        foreach ($components as $component) {
            Blade::component($component['view'], $component['alias']);
        }

        Blade::componentNamespace('FluxErp\\View\\Components', 'flux');
    }

    protected function registerConfig(): void
    {
        $this->booted(function (): void {
            config([
                'tallstackui.settings.toast.z-index' => 'z-50',
                'tallstackui.settings.toast.timeout' => 5,
                'tallstackui.settings.dialog.z-index' => 'z-40',
                'tallstackui.settings.modal.z-index' => 'z-30',
                'tallstackui.settings.slide.z-index' => 'z-30',
            ]);
            config(['permission.models.role' => resolve_static(Role::class, 'class')]);
            config(['permission.models.permission' => resolve_static(Permission::class, 'class')]);
            config(['permission.display_permission_in_exception' => true]);
            config(['activitylog.activitymodel' => resolve_static(Activity::class, 'class')]);
            config(['media-library.media_downloader' => MediaLibraryDownloader::class]);
        });
        $this->mergeConfigFrom(__DIR__ . '/../config/flux.php', 'flux');
        $this->mergeConfigFrom(__DIR__ . '/../config/notifications.php', 'notifications');
        config(['auth' => require __DIR__ . '/../config/auth.php']);

        if (! app()->configurationIsCached()) {
            config(['logging' => array_merge_recursive(config('logging'), require __DIR__ . '/../config/logging.php')]);
        }

        if ($this->app->runningInConsole()) {
            config([
                'tinker.alias' => [
                    'FluxErp\\Models\\',
                    'FluxErp\\Actions\\',
                ],
            ]);
        }
    }

    protected function registerLivewireComponents(): void
    {
        $livewireNamespace = 'FluxErp\\Livewire\\';

        foreach ($this->getViewClassAliasFromNamespace($livewireNamespace) as $alias => $class) {
            try {
                if (is_a($class, Component::class, true)
                    && ! (new ReflectionClass($class))->isAbstract()
                ) {
                    Livewire::component($alias, $class);
                }
            } catch (Throwable) {
                // Skip invalid components
            }
        }
    }

    private function bootMiddleware(): void
    {
        /** @var Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddlewareToGroup('api', EnsureFrontendRequestsAreStateful::class);

        $kernel->appendMiddlewareToGroup('web', Localization::class);
        $kernel->appendMiddlewareToGroup('web', AuthContextMiddleware::class);
        $kernel->appendMiddlewareToGroup('web', PortalMiddleware::class);

        $this->app['router']->aliasMiddleware('ability', CheckForAnyAbility::class);
        $this->app['router']->aliasMiddleware('role', RoleMiddleware::class);
        $this->app['router']->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
        $this->app['router']->aliasMiddleware('permission', Permissions::class);
        $this->app['router']->aliasMiddleware('localization', Localization::class);

        Bus::pipeThrough([app(SetJobAuthenticatedUserMiddleware::class)]);
    }

    private function getViewClassAliasFromNamespace(string $namespace, ?string $directoryPath = null): array
    {
        if ($namespace === 'FluxErp\\Livewire\\') {
            $cachePath = $this->app->bootstrapPath('cache/flux-livewire-components.php');

            if (file_exists($cachePath)) {
                return require $cachePath;
            }
        }

        return once(function () use ($namespace, $directoryPath) {
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
                    array_walk($exploded, function (&$value): void {
                        $value = Str::snake(Str::remove('.php', $value), '-');
                    });

                    $alias = ltrim(implode('.', $exploded), '.');
                    $components[$alias] = $class;
                }
            }

            return $components;
        });
    }

    private function registerExtensions(): void
    {
        $this->app->extend(
            Dispatcher::class,
            function () {
                return new FluxDispatcher(
                    $this->app,
                    function ($connection = null) {
                        return $this->app[QueueFactoryContract::class]->connection($connection);
                    }
                );
            }
        );

        $this->app->extend(
            'composer',
            function () {
                return $this->app->get(Composer::class);
            }
        );
    }
}
