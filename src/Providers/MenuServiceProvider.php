<?php

namespace FluxErp\Providers;

use FluxErp\Facades\Menu;
use FluxErp\FluxServiceProvider;
use FluxErp\Menu\MenuManager;
use FluxErp\Models\OrderType;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->app->bound(MenuManager::class)) {
            $this->app->singleton(MenuManager::class, fn (): MenuManager => new MenuManager());
        }
    }

    public function boot(): void
    {
        if (FluxServiceProvider::$registerFluxRoutes) {
            $this->bootFluxMenu();
        }

        if (FluxServiceProvider::$registerPortalRoutes) {
            $this->bootPortalMenu();
        }
    }

    protected function bootFluxMenu(): void
    {
        Menu::register(route: 'dashboard', icon: 'home', order: -9999);

        Menu::group(
            path: 'sales',
            icon: 'shopping-cart',
            label: 'Sales',
            closure: function (): void {
                Menu::register(route: 'sales.leads');
            }
        );

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
                Menu::register(route: 'contacts.addresses');
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
                Menu::register(route: 'accounting.transaction-assignments');
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
        Menu::register(route: 'settings', icon: 'cog', label: 'settings', order: 9999);

        Menu::group(
            path: 'settings',
            icon: 'cog',
            label: 'Settings',
            order: 9999,
            closure: function (): void {
                Menu::register(route: 'settings.system');
                Menu::register(route: 'settings.additional-columns');
                Menu::register(route: 'settings.address-types');
                Menu::register(route: 'settings.record-origins');
                Menu::register(route: 'settings.industries');
                Menu::register(route: 'settings.categories');
                Menu::register(route: 'settings.tags');
                Menu::register(route: 'settings.targets');
                Menu::register(route: 'settings.lead-loss-reasons');
                Menu::register(route: 'settings.lead-states');
                Menu::register(route: 'settings.email-templates');
                Menu::register(route: 'settings.tokens');
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
                Menu::register(route: 'settings.core-settings');
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
}
