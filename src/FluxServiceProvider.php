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
use FluxErp\Models\Activity;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Category;
use FluxErp\Models\Client;
use FluxErp\Models\Comment;
use FluxErp\Models\Commission;
use FluxErp\Models\CommissionRate;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\ContactOption;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Currency;
use FluxErp\Models\CustomEvent;
use FluxErp\Models\Discount;
use FluxErp\Models\DiscountGroup;
use FluxErp\Models\DocumentGenerationSetting;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Email;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\Favorite;
use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Models\InterfaceUser;
use FluxErp\Models\Language;
use FluxErp\Models\LanguageLine;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Lock;
use FluxErp\Models\Log;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\Media;
use FluxErp\Models\Meta;
use FluxErp\Models\Notification;
use FluxErp\Models\NotificationSetting;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentNotice;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\Project;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\Role;
use FluxErp\Models\Schedule;
use FluxErp\Models\SepaMandate;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\Setting;
use FluxErp\Models\Snapshot;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\Token;
use FluxErp\Models\Transaction;
use FluxErp\Models\Unit;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Widgets\WidgetManager;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        $this->registerMorphMap();
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

    private function registerMorphMap(): void
    {
        Relation::enforceMorphMap([
            'activity' => Activity::class,
            'additional_column' => AdditionalColumn::class,
            'address' => Address::class,
            'address_type' => AddressType::class,
            'bank_connection' => BankConnection::class,
            'calendar' => Calendar::class,
            'calendar_event' => CalendarEvent::class,
            'category' => Category::class,
            'client' => Client::class,
            'comment' => Comment::class,
            'commission' => Commission::class,
            'commission_rate' => CommissionRate::class,
            'communication' => Communication::class,
            'contact' => Contact::class,
            'contact_bank_connection' => ContactBankConnection::class,
            'contact_option' => ContactOption::class,
            'country' => Country::class,
            'country_region' => CountryRegion::class,
            'currency' => Currency::class,
            'custom_event' => CustomEvent::class,
            'discount' => Discount::class,
            'discount_group' => DiscountGroup::class,
            'document_generation_setting' => DocumentGenerationSetting::class,
            'document_type' => DocumentType::class,
            'email' => Email::class,
            'email_template' => EmailTemplate::class,
            'event_subscription' => EventSubscription::class,
            'favorite' => Favorite::class,
            'form_builder_field' => FormBuilderField::class,
            'form_builder_field_response' => FormBuilderFieldResponse::class,
            'form_builder_form' => FormBuilderForm::class,
            'form_builder_response' => FormBuilderResponse::class,
            'form_builder_section' => FormBuilderSection::class,
            'interface_user' => InterfaceUser::class,
            'language' => Language::class,
            'translation' => LanguageLine::class,
            'ledger_account' => LedgerAccount::class,
            'lock' => Lock::class,
            'log' => Log::class,
            'meta' => Meta::class,
            'mail_account' => MailAccount::class,
            'mail_folder' => MailFolder::class,
            'media' => Media::class,
            'notification' => Notification::class,
            'notification_setting' => NotificationSetting::class,
            'order' => Order::class,
            'order_position' => OrderPosition::class,
            'order_type' => OrderType::class,
            'payment_notice' => PaymentNotice::class,
            'payment_reminder' => PaymentReminder::class,
            'payment_run' => PaymentRun::class,
            'payment_type' => PaymentType::class,
            'permission' => Permission::class,
            'price' => Price::class,
            'price_list' => PriceList::class,
            'product' => Product::class,
            'product_cross_selling' => ProductCrossSelling::class,
            'product_option' => ProductOption::class,
            'product_option_group' => ProductOptionGroup::class,
            'product_property' => ProductProperty::class,
            'project' => Project::class,
            'purchase_invoice' => PurchaseInvoice::class,
            'purchase_invoice_position' => PurchaseInvoicePosition::class,
            'role' => Role::class,
            'schedule' => Schedule::class,
            'sepa_mandate' => SepaMandate::class,
            'serial_number' => SerialNumber::class,
            'serial_number_range' => SerialNumberRange::class,
            'setting' => Setting::class,
            'snapshot' => Snapshot::class,
            'stock_posting' => StockPosting::class,
            'tag' => Tag::class,
            'task' => Task::class,
            'ticket' => Ticket::class,
            'ticket_type' => TicketType::class,
            'token' => Token::class,
            'transaction' => Transaction::class,
            'unit' => Unit::class,
            'user' => User::class,
            'vat_rate' => VatRate::class,
            'warehouse' => Warehouse::class,
            'widget' => Models\Widget::class,
            'work_time' => WorkTime::class,
            'work_time_type' => WorkTimeType::class,
        ]);
    }
}
