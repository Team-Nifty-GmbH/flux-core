<?php

namespace FluxErp\Providers;

use FluxErp\Facades\Menu;
use FluxErp\FluxServiceProvider;
use FluxErp\Menu\MenuManager;
use FluxErp\Models\OrderType;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (FluxServiceProvider::$registerFluxRoutes) {
            $this->bootFluxMenu();
        }
    }

    public function register(): void
    {
        $this->app->singleton(MenuManager::class);
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

        Menu::group(
            path: 'human-resources',
            icon: 'users',
            label: 'Human Resources',
            closure: function (): void {
                Menu::register(route: 'human-resources.my-employee-profile');
                Menu::register(route: 'human-resources.dashboard');
                Menu::register(route: 'human-resources.attendance-overview');
                Menu::register(route: 'human-resources.employees');
                Menu::register(route: 'human-resources.employee-days');
                Menu::register(route: 'human-resources.work-times');
                Menu::register(route: 'human-resources.absence-requests');
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
                // General
                Menu::group(path: 'settings.children.general', label: 'General');
                Menu::register(route: 'settings.categories', path: 'settings.children.general.children.categories');
                Menu::register(route: 'settings.countries', path: 'settings.children.general.children.countries');
                Menu::register(route: 'settings.country-regions', path: 'settings.children.general.children.country-regions');
                Menu::register(route: 'settings.currencies', path: 'settings.children.general.children.currencies');
                Menu::register(route: 'settings.languages', path: 'settings.children.general.children.languages');
                Menu::register(route: 'settings.record-origins', path: 'settings.children.general.children.record-origins');
                Menu::register(route: 'settings.serial-number-ranges', path: 'settings.children.general.children.serial-number-ranges');
                Menu::register(route: 'settings.tags', path: 'settings.children.general.children.tags');
                Menu::register(route: 'settings.tenants', path: 'settings.children.general.children.tenants');

                // Tickets
                Menu::group(path: 'settings.children.tickets', label: 'Tickets');
                Menu::register(route: 'settings.ticket-settings', path: 'settings.children.tickets.children.ticket-settings');
                Menu::register(route: 'settings.ticket-types', path: 'settings.children.tickets.children.ticket-types');

                // Orders
                Menu::group(path: 'settings.children.orders', label: 'Orders');
                Menu::register(route: 'settings.discount-groups', path: 'settings.children.orders.children.discount-groups');
                Menu::register(route: 'settings.order-types', path: 'settings.children.orders.children.order-types');
                Menu::register(route: 'settings.payment-types', path: 'settings.children.orders.children.payment-types');

                // Products
                Menu::group(path: 'settings.children.products', label: 'Products');
                Menu::register(route: 'settings.price-lists', path: 'settings.children.products.children.price-lists');
                Menu::register(route: 'settings.product-option-groups', path: 'settings.children.products.children.product-option-groups');
                Menu::register(route: 'settings.product-properties', path: 'settings.children.products.children.product-properties');
                Menu::register(route: 'settings.units', path: 'settings.children.products.children.units');
                Menu::register(route: 'settings.warehouses', path: 'settings.children.products.children.warehouses');

                // Contacts
                Menu::group(path: 'settings.children.contacts', label: 'Contacts');
                Menu::register(route: 'settings.address-types', path: 'settings.children.contacts.children.address-types');
                Menu::register(route: 'settings.industries', path: 'settings.children.contacts.children.industries');

                // Sales
                Menu::group(path: 'settings.children.sales', label: 'Sales');
                Menu::register(route: 'settings.lead-loss-reasons', path: 'settings.children.sales.children.lead-loss-reasons');
                Menu::register(route: 'settings.lead-states', path: 'settings.children.sales.children.lead-states');
                Menu::register(route: 'settings.targets', path: 'settings.children.sales.children.targets');

                // Tasks
                Menu::group(path: 'settings.children.tasks', label: 'Tasks');
                Menu::register(route: 'settings.reminder-settings', path: 'settings.children.tasks.children.reminder-settings');

                // Human Resources
                Menu::group(path: 'settings.children.human-resources', label: 'Human Resources');
                Menu::register(route: 'settings.absence-policies', path: 'settings.children.human-resources.children.absence-policies');
                Menu::register(route: 'settings.absence-types', path: 'settings.children.human-resources.children.absence-types');
                Menu::register(route: 'settings.employee-departments', path: 'settings.children.human-resources.children.employee-departments');
                Menu::register(route: 'settings.holidays', path: 'settings.children.human-resources.children.holidays');
                Menu::register(route: 'settings.locations', path: 'settings.children.human-resources.children.locations');
                Menu::register(route: 'settings.salary-components', path: 'settings.children.human-resources.children.salary-components');
                Menu::register(route: 'settings.vacation-blackouts', path: 'settings.children.human-resources.children.vacation-blackouts');
                Menu::register(route: 'settings.vacation-carryover-rules', path: 'settings.children.human-resources.children.vacation-carryover-rules');
                Menu::register(route: 'settings.work-time-models', path: 'settings.children.human-resources.children.work-time-models');
                Menu::register(route: 'settings.work-time-types', path: 'settings.children.human-resources.children.work-time-types');

                // Accounting
                Menu::group(path: 'settings.children.accounting', label: 'Accounting');
                Menu::register(route: 'settings.accounting-settings', path: 'settings.children.accounting.children.accounting-settings');
                Menu::register(route: 'settings.bank-connections', path: 'settings.children.accounting.children.bank-connections');
                Menu::register(route: 'settings.ledger-accounts', path: 'settings.children.accounting.children.ledger-accounts');
                Menu::register(route: 'settings.payment-reminder-texts', path: 'settings.children.accounting.children.payment-reminder-texts');
                Menu::register(route: 'settings.vat-rates', path: 'settings.children.accounting.children.vat-rates');

                // Communication
                Menu::group(path: 'settings.children.communication', label: 'Communication');
                Menu::register(route: 'settings.email-templates', path: 'settings.children.communication.children.email-templates');
                Menu::register(route: 'settings.mail-accounts', path: 'settings.children.communication.children.mail-accounts');
                Menu::register(route: 'settings.notifications', path: 'settings.children.communication.children.notifications');

                // Users & Permissions
                Menu::group(path: 'settings.children.users-permissions', label: 'Users & Permissions');
                Menu::register(route: 'settings.permissions', path: 'settings.children.users-permissions.children.permissions');
                Menu::register(route: 'settings.tokens', path: 'settings.children.users-permissions.children.tokens');
                Menu::register(route: 'settings.users', path: 'settings.children.users-permissions.children.users');

                // System
                Menu::group(path: 'settings.children.system', label: 'System');
                Menu::register(route: 'settings.activity-logs', path: 'settings.children.system.children.activity-logs');
                Menu::register(route: 'settings.core-settings', path: 'settings.children.system.children.core-settings');
                Menu::register(route: 'settings.failed-jobs', path: 'settings.children.system.children.failed-jobs');
                Menu::register(route: 'settings.logs', path: 'settings.children.system.children.logs');
                Menu::register(route: 'settings.plugins', path: 'settings.children.system.children.plugins');
                Menu::register(route: 'settings.print-jobs', path: 'settings.children.system.children.print-jobs');
                Menu::register(route: 'settings.printers', path: 'settings.children.system.children.printers');
                Menu::register(route: 'settings.queue-monitor', path: 'settings.children.system.children.queue-monitor');
                Menu::register(route: 'settings.scheduling', path: 'settings.children.system.children.scheduling');
                Menu::register(route: 'settings.system', path: 'settings.children.system.children.system-settings');
            }
        );
    }
}
