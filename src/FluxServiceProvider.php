<?php

namespace FluxErp;

use Closure;
use FluxErp\Console\Commands\Init\InitEnv;
use FluxErp\Console\Commands\Init\InitPermissions;
use FluxErp\Facades\Action;
use FluxErp\Facades\Menu;
use FluxErp\Facades\ProductType;
use FluxErp\Facades\Repeatable;
use FluxErp\Facades\Widget;
use FluxErp\Helpers\Composer;
use FluxErp\Helpers\Livewire\Features\SupportFormObjects;
use FluxErp\Helpers\MediaLibraryDownloader;
use FluxErp\Http\Middleware\AuthContextMiddleware;
use FluxErp\Http\Middleware\Localization;
use FluxErp\Http\Middleware\Permissions;
use FluxErp\Http\Middleware\PortalMiddleware;
use FluxErp\Http\Middleware\SetJobAuthenticatedUserMiddleware;
use FluxErp\Livewire\Features\Calendar\CalendarOverview;
use FluxErp\Models\Activity;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Client;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Notification;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\Project;
use FluxErp\Models\Role;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Support\Validator\ValidatorFactory;
use FluxErp\Traits\HasClientAssignment;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Bus\Dispatcher;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Laravel\Scout\Builder;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
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
        bcscale(9);
        $this->bootMiddleware();
        $this->bootCommands();

        $this->optimizes('flux:optimize', 'flux:optimize-clear');

        $this->bootRoutes();
        $this->registerLivewireComponents();
        $this->registerBladeComponents();

        if (static::$registerFluxRoutes && (! $this->app->runningInConsole() || $this->app->runningUnitTests())) {
            $this->bootFluxMenu();
        }

        if (static::$registerPortalRoutes && (! $this->app->runningInConsole() || $this->app->runningUnitTests())) {
            $this->bootPortalMenu();
        }

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
        // Register repeatable invokable classes from "Invokable" directory
        Repeatable::autoDiscover(flux_path('src/Invokable'), 'FluxErp\Invokable');
        // Register repeatable artisan commands, jobs and invokable classes (in "Repeatable" directory) from app
        Repeatable::autoDiscover();

        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            ProductType::register(name: 'product', class: \FluxErp\Livewire\Product\Product::class, default: true);
        }

        Livewire::component('calendar-overview', CalendarOverview::class);
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
        $this->registerMarcos();
        $this->registerExtensions();

        Translatable::fallback(
            fallbackAny: true,
        );

        app('livewire')->componentHook(SupportFormObjects::class);
        $this->app->bind(DatabaseNotification::class, Notification::class);
    }

    protected function bootCommands(): void
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

    protected function bootFluxMenu(): void
    {
        Menu::register(route: 'dashboard', icon: 'home', order: -9999);

        Menu::group(
            path: 'orders',
            icon: 'briefcase',
            label: 'Orders',
            closure: function (): void {
                foreach (resolve_static(OrderType::class, 'query')
                    ->where('is_visible_in_sidebar', true)
                    ->where('is_active', true)
                    ->get(['id', 'name']) as $orderType
                ) {
                    Menu::register(
                        route: 'orders.order-type',
                        label: $orderType->name,
                        params: ['orderType' => $orderType->id],
                        path: 'orders.children.order-type-' . $orderType->id
                    );
                }
                Menu::register(route: 'orders.orders', label: __('All orders'));
                Menu::register(route: 'orders.order-positions');
            }
        );

        Menu::group(
            path: 'contacts',
            icon: 'identification',
            label: 'Contacts',
            closure: function (): void {
                Menu::register(route: 'contacts.contacts');
                Menu::register(route: 'contacts.communications');
            }
        );

        Menu::register(route: 'tasks', icon: 'clipboard-document');
        Menu::register(route: 'tickets', icon: 'wrench-screwdriver');
        Menu::register(route: 'projects', icon: 'briefcase');

        Menu::group(
            path: 'accounting',
            icon: 'banknotes',
            label: 'Accounting',
            closure: function (): void {
                Menu::register(route: 'accounting.work-times');
                Menu::register(route: 'accounting.commissions');
                Menu::register(route: 'accounting.payment-reminders');
                Menu::register(route: 'accounting.purchase-invoices');
                Menu::register(route: 'accounting.transactions');
                Menu::register(route: 'accounting.direct-debit');
                Menu::register(route: 'accounting.money-transfer');
                Menu::register(route: 'accounting.payment-runs');
            }
        );

        Menu::group(
            path: 'products',
            icon: 'square-3-stack-3d',
            label: 'Products',
            closure: function (): void {
                Menu::register(route: 'products.products');
                Menu::register(route: 'products.serial-numbers');
            }
        );

        Menu::register(route: 'mail', icon: 'envelope');
        Menu::register(route: 'calendars', icon: 'calendar');

        Menu::register(route: 'media-grid', icon: 'photo', label: 'media');
        Menu::register(route: 'settings', icon: 'cog', label: 'settings');

        Menu::group(
            path: 'settings',
            icon: 'cog',
            label: 'Settings',
            order: 9999,
            closure: function (): void {
                Menu::register(route: 'settings.additional-columns');
                Menu::register(route: 'settings.address-types');
                Menu::register(route: 'settings.contact-origins');
                Menu::register(route: 'settings.industries');
                Menu::register(route: 'settings.categories');
                Menu::register(route: 'settings.tags');
                Menu::register(route: 'settings.product-option-groups');
                Menu::register(route: 'settings.product-properties');
                Menu::register(route: 'settings.clients');
                Menu::register(route: 'settings.bank-connections');
                Menu::register(route: 'settings.countries');
                Menu::register(route: 'settings.currencies');
                Menu::register(route: 'settings.discount-groups');
                Menu::register(route: 'settings.languages');
                Menu::register(route: 'settings.ledger-accounts');
                Menu::register(route: 'settings.logs');
                Menu::register(route: 'settings.activity-logs');
                Menu::register(route: 'settings.notifications');
                Menu::register(route: 'settings.order-types');
                Menu::register(route: 'settings.permissions');
                Menu::register(route: 'settings.price-lists');
                Menu::register(route: 'settings.print-jobs');
                Menu::register(route: 'settings.printers');
                Menu::register(route: 'settings.ticket-types');
                Menu::register(route: 'settings.translations');
                Menu::register(route: 'settings.units');
                Menu::register(route: 'settings.users');
                Menu::register(route: 'settings.mail-accounts');
                Menu::register(route: 'settings.work-time-types');
                Menu::register(route: 'settings.vat-rates');
                Menu::register(route: 'settings.payment-types');
                Menu::register(route: 'settings.payment-reminder-texts');
                Menu::register(route: 'settings.warehouses');
                Menu::register(route: 'settings.serial-number-ranges');
                Menu::register(route: 'settings.scheduling');
                Menu::register(route: 'settings.queue-monitor');
                Menu::register(route: 'settings.failed-jobs');
                Menu::register(route: 'settings.plugins');
            }
        );
    }

    protected function bootPortalMenu(): void
    {
        Menu::register(route: 'portal.dashboard', icon: 'home', order: -9999);
        Menu::register(route: 'portal.calendar', icon: 'calendar');
        Menu::register(route: 'portal.products', icon: 'square-3-stack-3d');
        Menu::register(route: 'portal.files', icon: 'folder-open');
        Menu::register(route: 'portal.orders', icon: 'shopping-bag');
        Menu::register(route: 'portal.tickets', icon: 'wrench-screwdriver');
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

        Blade::componentNamespace('FluxErp\\View\\Components', 'flux');
    }

    protected function registerConfig(): void
    {
        $this->booted(function (): void {
            config([
                'tallstackui.settings.toast.z-index' => 'z-50',
                'tallstackui.settings.dialog.z-index' => 'z-40',
                'tallstackui.settings.modal.z-index' => 'z-30',
            ]);
            config(['permission.models.role' => resolve_static(Role::class, 'class')]);
            config(['permission.models.permission' => resolve_static(Permission::class, 'class')]);
            config(['permission.display_permission_in_exception' => true]);
            config(['activitylog.activitymodel' => resolve_static(Activity::class, 'class')]);
            config(['media-library.media_downloader' => MediaLibraryDownloader::class]);
            config([
                'scout.meilisearch.index-settings' => [
                    resolve_static(Address::class, 'class') => [
                        'filterableAttributes' => [
                            'is_main_address',
                            'contact_id',
                        ],
                        'sortableAttributes' => ['*'],
                    ],
                    resolve_static(Category::class, 'class') => [
                        'filterableAttributes' => [
                            'model_type',
                        ],
                    ],
                    resolve_static(LedgerAccount::class, 'class') => [
                        'filterableAttributes' => [
                            'ledger_account_type_enum',
                            'is_automatic',
                        ],
                        'sortableAttributes' => ['*'],
                    ],
                    resolve_static(Order::class, 'class') => [
                        'filterableAttributes' => [
                            'parent_id',
                            'contact_id',
                            'is_locked',
                        ],
                        'sortableAttributes' => ['*'],
                    ],
                    resolve_static(Permission::class, 'class') => [
                        'filterableAttributes' => [
                            'guard_name',
                        ],
                        'sortableAttributes' => [
                            'name',
                        ],
                    ],
                    resolve_static(Product::class, 'class') => [
                        'filterableAttributes' => [
                            'is_active',
                            'parent_id',
                        ],
                        'sortableAttributes' => ['*'],
                    ],
                    resolve_static(Project::class, 'class') => [
                        'filterableAttributes' => [
                            'parent_id',
                            'state',
                        ],
                        'sortableAttributes' => ['*'],
                    ],
                    resolve_static(SerialNumber::class, 'class') => [
                        'filterableAttributes' => [
                            'address_id',
                        ],
                    ],
                    resolve_static(Task::class, 'class') => [
                        'filterableAttributes' => [
                            'project_id',
                            'state',
                        ],
                        'sortableAttributes' => ['*'],
                    ],
                    resolve_static(Ticket::class, 'class') => [
                        'filterableAttributes' => [
                            'authenticatable_type',
                            'authenticatable_id',
                            'state',
                        ],
                        'sortableAttributes' => ['*'],
                    ],
                    resolve_static(User::class, 'class') => [
                        'filterableAttributes' => [
                            'is_active',
                        ],
                    ],
                ],
            ]);
        });
        $this->mergeConfigFrom(__DIR__ . '/../config/flux.php', 'flux');
        $this->mergeConfigFrom(__DIR__ . '/../config/notifications.php', 'notifications');
        config(['auth' => require __DIR__ . '/../config/auth.php']);
        config(['logging' => array_merge_recursive(config('logging'), require __DIR__ . '/../config/logging.php')]);

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
                Cache::forget('flux.view-classes.' . Str::slug($livewireNamespace));
            }
        }
    }

    protected function registerMarcos(): void
    {
        if (! Arr::hasMacro('sortByPattern')) {
            Arr::macro('sortByPattern', function (array $array, array $pattern) {
                $sortedAttributes = [];
                foreach ($pattern as $key) {
                    if (array_key_exists($key, $array)) {
                        $sortedAttributes[$key] = Arr::pull($array, $key);
                    }
                }

                // Merge the sorted attributes with the remaining attributes
                return array_merge($sortedAttributes, $array);
            });
        }

        if (! Arr::hasMacro('undotToTree')) {
            Arr::macro(
                'undotToTree',
                function (array $array, string $path = '', ?Closure $translate = null): array {
                    $array = Arr::undot($array);
                    $translate = $translate ?: fn ($key) => __(Str::headline($key));
                    $buildTree = function (array $array, string $path = '') use (&$buildTree, $translate) {
                        $tree = [];

                        foreach ($array as $key => $value) {
                            $currentPath = $path === '' ? $key : $path . '.' . $key;

                            if (is_array($value)) {
                                $tree[] = [
                                    'id' => $currentPath,
                                    'label' => $translate($key),
                                    'children' => $buildTree($value, $currentPath),
                                ];
                            } else {
                                $tree[] = [
                                    'id' => $currentPath,
                                    'label' => $translate($key),
                                    'value' => $value,
                                ];
                            }
                        }

                        return $tree;
                    };

                    return $buildTree($array, $path);
                }
            );
        }

        if (! Str::hasMacro('iban')) {
            Str::macro('iban', function (?string $iban) {
                return trim(chunk_split($iban ?? '', 4, ' '));
            });
        }

        if (! Request::hasMacro('isPortal')) {
            Request::macro('isPortal', function () {
                // check if the current url matches with config('flux.portal_domain')
                // ignore http or https, just match the host itself
                return Str::startsWith($this->getHost(), Str::after(config('flux.portal_domain'), '://'));
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

        if (! Number::hasMacro('fromFileSizeToBytes')) {
            Number::macro('fromFileSizeToBytes',
                function (string $size) {
                    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
                    preg_match('/^(\d+)([A-Z]{1,2})$/i', trim($size), $matches);

                    if (count($matches) !== 3) {
                        throw new InvalidArgumentException("Invalid size format: $size");
                    }

                    $numericPart = $matches[1];
                    $unit = strtoupper($matches[2]);

                    if (strlen($unit) === 1) {
                        $unit .= 'B';
                    }

                    $power = array_search($unit, $units);

                    if ($power === false) {
                        throw new InvalidArgumentException("Invalid size unit provided: $unit");
                    }

                    return bcmul($numericPart, bcpow('1024', $power), 0);
                });
        }

        if ($this->app->runningUnitTests()) {
            if (! Testable::hasMacro('assertToastNotification')) {
                Testable::macro(
                    'assertToastNotification',
                    function (
                        ?string $title = null,
                        ?string $type = null,
                        ?string $description = null,
                        ?bool $expandable = null,
                        ?int $timeout = null,
                        ?bool $persistent = null,
                        string|int|null $id = null
                    ) {
                        $this->assertDispatched(
                            'tallstackui:toast',
                            function (
                                string $eventName,
                                array $params
                            ) use ($title, $type, $description, $expandable, $timeout, $persistent, $id) {
                                return array_key_exists('component', $params)
                                    && (is_null($type) || data_get($params, 'type') === $type)
                                    && (is_null($title) || data_get($params, 'title') === $title)
                                    && (is_null($description) || data_get($params, 'description') === $description)
                                    && (is_null($expandable) || data_get($params, 'expandable') === $expandable)
                                    && (is_null($timeout) || data_get($params, 'timeout') === $timeout)
                                    && (is_null($persistent) || data_get($params, 'persistent') === $persistent)
                                    && (is_null($id) || data_get($params, 'persistent') === $id);
                            }
                        );

                        return $this;
                    }
                );
            }

            if (! Testable::hasMacro('assertExecutesJs')) {
                Testable::macro(
                    'assertExecutesJs',
                    function (string $js) {
                        Assert::assertStringContainsString(
                            $js,
                            implode(' ', data_get($this->lastState->getEffects(), 'xjs.*.expression', []))
                        );

                        return $this;
                    }
                );
            }
        }

        Route::macro('getPermissionName',
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

        Route::macro('hasPermission', function () {
            $this->setAction(array_merge($this->getAction(), [
                'permission' => route_to_permission($this, false),
            ]));

            return $this;
        });

        Command::macro('removeLastLine', function (): void {
            $this->output->write("\x1b[1A\r\x1b[K");
        });
    }

    private function bootMiddleware(): void
    {
        /** @var Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddlewareToGroup('api', EnsureFrontendRequestsAreStateful::class);

        $kernel->appendMiddlewareToGroup('web', Localization::class);
        $kernel->appendMiddlewareToGroup('web', AuthContextMiddleware::class);
        $kernel->appendMiddlewareToGroup('web', PortalMiddleware::class);

        $this->app['router']->aliasMiddleware('abilities', CheckAbilities::class);
        $this->app['router']->aliasMiddleware('role', RoleMiddleware::class);
        $this->app['router']->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
        $this->app['router']->aliasMiddleware('permission', Permissions::class);
        $this->app['router']->aliasMiddleware('localization', Localization::class);

        Bus::pipeThrough([app(SetJobAuthenticatedUserMiddleware::class)]);
    }

    private function getViewClassAliasFromNamespace(string $namespace, ?string $directoryPath = null): array
    {
        if (Cache::has('flux.view-classes.' . Str::slug($namespace))) {
            return Cache::get('flux.view-classes.' . Str::slug($namespace));
        }

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

        return Cache::rememberForever('flux.view-classes.' . Str::slug($namespace), fn () => $components);
    }

    private function registerExtensions(): void
    {
        $this->app->extend(
            Dispatcher::class,
            function () {
                return new Support\Bus\Dispatcher(
                    $this->app,
                    function ($connection = null) {
                        return $this->app[QueueFactoryContract::class]->connection($connection);
                    }
                );
            }
        );

        $this->app->extend(
            Builder::class,
            function (Builder $scoutBuilder) {
                if (($user = auth()->user()) instanceof User
                    && in_array(HasClientAssignment::class, class_uses_recursive($scoutBuilder->model))
                    && $scoutBuilder->model->isRelation('client')
                    && ($relation = $scoutBuilder->model->client()) instanceof BelongsTo
                ) {
                    $clients = $user->clients()->pluck('id')->toArray() ?: Client::query()->pluck('id')->toArray();

                    $scoutBuilder->whereIn($relation->getForeignKeyName(), $clients);
                }

                return $scoutBuilder;
            }
        );

        $this->app->extend(
            'composer',
            function () {
                return $this->app->get(Composer::class);
            }
        );

        $this->app->extend(
            'validator',
            function () {
                return $this->app->get(ValidatorFactory::class);
            }
        );
    }
}
