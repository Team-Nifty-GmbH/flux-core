<?php

use FluxErp\Facades\Menu;
use FluxErp\Models\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

function registerMenuProbe(string $name, string $label): string
{
    Route::middleware('auth:web')->get($name, fn () => null)->name($name);
    app('router')->getRoutes()->refreshNameLookups();
    Menu::register(route: $name, label: $label);

    return $name . '.get';
}

function menuLabels(array $menu): array
{
    $labels = [];

    foreach ($menu as $item) {
        if ($label = data_get($item, 'label')) {
            $labels[] = $label;
        }

        $labels = array_merge($labels, menuLabels(data_get($item, 'children') ?? []));
    }

    return $labels;
}

beforeEach(function (): void {
    Menu::clear();
    Auth::login($this->user);
});

test('hides an entry whose permission the user does not have', function (): void {
    $permission = registerMenuProbe('menu-permission-probe', 'Permission Probe Page');
    Permission::findOrCreate($permission, 'web');

    expect(menuLabels(Menu::forGuard('web')))->not->toContain('Permission Probe Page');
});

test('shows an entry whose permission the user has', function (): void {
    $permission = registerMenuProbe('menu-permission-probe', 'Permission Probe Page');
    $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));

    expect(menuLabels(Menu::forGuard('web')))->toContain('Permission Probe Page');
});

test('shows an entry whose permission was never created', function (): void {
    registerMenuProbe('menu-permission-probe', 'Permission Probe Page');

    expect(menuLabels(Menu::forGuard('web')))->toContain('Permission Probe Page');
});

test('shows every entry when a gate grants all abilities', function (): void {
    $permission = registerMenuProbe('menu-permission-probe', 'Permission Probe Page');
    Permission::findOrCreate($permission, 'web');

    Gate::before(fn (Authenticatable $user, string $ability): ?true => true);

    expect(menuLabels(Menu::forGuard('web')))->toContain('Permission Probe Page');
});

test('ignores permissions entirely when asked to', function (): void {
    $permission = registerMenuProbe('menu-permission-probe', 'Permission Probe Page');
    Permission::findOrCreate($permission, 'web');

    expect(menuLabels(Menu::forGuard('web', ignorePermissions: true)))->toContain('Permission Probe Page');
});

test('asks the gate once no matter how many entries carry a permission', function (): void {
    foreach (range(1, 20) as $index) {
        Permission::findOrCreate(
            registerMenuProbe('menu-gate-probe-' . $index, 'Gate Probe Page ' . $index),
            'web'
        );
    }

    $checks = 0;
    Gate::before(function () use (&$checks): null {
        $checks++;

        return null;
    });

    Menu::forGuard('web');

    expect(1)->toBe($checks);
});
