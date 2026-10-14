<?php

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Console\Commands\Init\InitPermissions;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\Token;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

test('creates a permission for every action, model, route, widget, tab and print view', function (): void {
    Artisan::call(InitPermissions::class);

    $permissions = Permission::query()->get(['name', 'guard_name']);
    $prefixed = ['action', 'model', 'print', 'tab', 'widget'];
    $prefixes = $permissions->pluck('name')->map(fn (string $name): string => strtok($name, '.'))->unique();

    expect($prefixes->intersect($prefixed)->sort()->values()->all())->toBe($prefixed)
        ->and($prefixes->diff($prefixed)->all())->not->toBe([])
        ->and($permissions->pluck('guard_name')->unique()->sort()->values()->all())->toBe(['sanctum', 'token', 'web'])
        ->and($permissions->where('name', 'action.order.create')->pluck('guard_name')->sort()->values()->all())
        ->toBe(['sanctum', 'token', 'web'])
        ->and($permissions->pluck('name'))->toContain('model.order.get');
});

test('running it twice changes nothing', function (): void {
    Artisan::call(InitPermissions::class);
    $first = Permission::query()->orderBy('guard_name')->orderBy('name')->get(['name', 'guard_name'])->toArray();

    Artisan::call(InitPermissions::class);
    $second = Permission::query()->orderBy('guard_name')->orderBy('name')->get(['name', 'guard_name'])->toArray();

    expect($first)->toBe($second);
});

test('does not reload the permission table once per permission', function (): void {
    $reads = 0;

    DB::listen(function (QueryExecuted $query) use (&$reads): void {
        if (str_contains($query->sql, 'from `permissions`')) {
            $reads++;
        }
    });

    Artisan::call(InitPermissions::class);

    expect($reads)->toBeLessThan(50)
        ->and(Permission::query()->count())->toBeGreaterThan(1000);
});

function forgetActionPermission(string $name): void
{
    Permission::query()->where('name', $name)->delete();
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

test('a role keeps an action whose permission it creates', function (): void {
    $permission = 'action.' . CreateContact::name();
    $role = Role::findOrCreate('Sales', 'web');
    $sanctumRole = Role::findOrCreate('Sales', 'sanctum');
    $this->user->assignRole($role);
    forgetActionPermission($permission);
    Auth::setUser($this->user->fresh());

    expect(CreateContact::canPerformAction(false))->toBeTrue();

    Artisan::call(InitPermissions::class);
    Auth::setUser($this->user->fresh());

    expect(CreateContact::canPerformAction(false))->toBeTrue()
        ->and($role->fresh()->hasPermissionTo($permission, 'web'))->toBeTrue()
        ->and($sanctumRole->fresh()->hasPermissionTo($permission, 'sanctum'))->toBeTrue();
});

test('a user without a role keeps an action whose permission it creates', function (): void {
    $permission = 'action.' . CreateContact::name();
    forgetActionPermission($permission);
    Auth::setUser($this->user->fresh());

    expect(CreateContact::canPerformAction(false))->toBeTrue();

    Artisan::call(InitPermissions::class);
    Auth::setUser($this->user->fresh());

    expect(CreateContact::canPerformAction(false))->toBeTrue();
});

test('a role does not get an existing permission it was not given', function (): void {
    $permission = 'action.' . CreateContact::name();
    Permission::findOrCreate($permission, 'web');
    $role = Role::findOrCreate('Sales', 'web');
    $this->user->assignRole($role);

    Artisan::call(InitPermissions::class);
    Auth::setUser($this->user->fresh());

    expect(CreateContact::canPerformAction(false))->toBeFalse()
        ->and($role->fresh()->hasPermissionTo($permission, 'web'))->toBeFalse();
});

test('a token is not given an action permission it creates', function (): void {
    $permission = 'action.' . CreateContact::name();
    forgetActionPermission($permission);
    $token = Token::query()->create(['name' => 'integration']);

    Artisan::call(InitPermissions::class);
    Auth::setUser($token->fresh());

    expect(CreateContact::canPerformAction(false))->toBeFalse()
        ->and($token->fresh()->permissions)->toBeEmpty();
});

test('the super admin role is left alone and keeps every action', function (): void {
    $permission = 'action.' . CreateContact::name();
    $role = Role::findOrCreate('Super Admin', 'web');
    $this->user->assignRole($role);
    forgetActionPermission($permission);

    Artisan::call(InitPermissions::class);
    Auth::setUser($this->user->fresh());

    expect(CreateContact::canPerformAction(false))->toBeTrue()
        ->and($role->fresh()->permissions->pluck('name'))->not->toContain($permission);
});
