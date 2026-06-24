<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Facades\Menu;
use FluxErp\Livewire\Navigation;
use FluxErp\Models\Notification;
use FluxErp\Models\OrderType;
use FluxErp\Models\Permission;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Livewire;

function createNavigationNotification(?string $route): Notification
{
    return test()->user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test',
        'data' => is_null($route) ? [] : ['accept' => ['route' => $route]],
    ]);
}

test('renders successfully', function (): void {
    Livewire::test(Navigation::class)
        ->assertOk();
});

test('passes unread notification counts per menu area to the view', function (): void {
    createNavigationNotification('tickets');

    Livewire::actingAs($this->user)
        ->test(Navigation::class)
        ->assertViewHas('notificationCounts', fn (array $counts): bool => ($counts['tickets'] ?? 0) === 1);
});

test('passes unread notification counts per sub menu route to the view', function (): void {
    createNavigationNotification('accounting.payment-reminder-run');

    Livewire::actingAs($this->user)
        ->test(Navigation::class)
        ->assertViewHas(
            'childNotificationCounts',
            fn (array $counts): bool => ($counts['accounting.payment-reminder-run'] ?? 0) === 1
        );
});

test('counts a sub menu detail notification on its closest sub menu route', function (): void {
    createNavigationNotification('accounting.payment-reminder-run.show');

    Livewire::actingAs($this->user)
        ->test(Navigation::class)
        ->assertViewHas(
            'childNotificationCounts',
            fn (array $counts): bool => ($counts['accounting.payment-reminder-run'] ?? 0) === 1
        );
});

test('shows order types', function (): void {
    $orderTypes = OrderType::factory(5)
        ->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
            'is_visible_in_sidebar' => true,
        ]);

    Livewire::actingAs($this->user)
        ->test(Navigation::class)
        ->assertSee($orderTypes->map(fn ($orderType) => Str::headline($orderType->name))->toArray());

    $orderTypes->first()->update(['is_visible_in_sidebar' => false]);
    Menu::clear();

    Livewire::actingAs($this->user)
        ->test(Navigation::class)
        ->assertDontSee(Str::headline($orderTypes->first()->name));
});

test('navigation cache is invalidated when user permissions change', function (): void {
    Menu::clear();
    Route::middleware('auth:web')->get('navigation-cache-probe', fn () => null)
        ->name('navigation-cache-probe');
    Menu::register(route: 'navigation-cache-probe', label: 'Cache Probe Page');

    $permission = Permission::findOrCreate('navigation-cache-probe.get', 'web');

    // First render: user lacks the permission, the menu item must be filtered out
    // and the (empty) result gets cached in the session.
    Livewire::actingAs($this->user)
        ->test(Navigation::class)
        ->assertDontSee('Cache Probe Page');

    // Permission grant happens between the two renders — exactly the situation the
    // session-side menu cache used to swallow.
    $this->user->givePermissionTo($permission);

    Livewire::actingAs($this->user)
        ->test(Navigation::class)
        ->assertSee('Cache Probe Page');
});
