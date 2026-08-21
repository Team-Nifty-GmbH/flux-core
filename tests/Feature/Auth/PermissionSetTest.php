<?php

use FluxErp\Models\Permission;
use FluxErp\Support\Auth\PermissionSet;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    Auth::login($this->user);
});

test('allows a permission that was never created', function (): void {
    expect(PermissionSet::make()->allows('never.created'))->toBeTrue();
});

test('denies a permission the user does not have', function (): void {
    Permission::findOrCreate('probe.denied', 'web');

    expect(PermissionSet::make()->allows('probe.denied'))->toBeFalse();
});

test('allows a permission the user has', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('probe.granted', 'web'));

    expect(PermissionSet::make()->allows('probe.granted'))->toBeTrue();
});

test('allows everything when a gate grants all abilities', function (): void {
    Permission::findOrCreate('probe.denied', 'web');

    Gate::before(fn (Authenticatable $user, string $ability): ?true => true);

    expect(PermissionSet::make()->allows('probe.denied'))->toBeTrue();
});

test('the number of stored permission scans does not grow with the number of checks', function (): void {
    foreach (range(1, 60) as $index) {
        Permission::findOrCreate('probe.many.' . $index, 'web');
    }

    $ask = fn (int $count): int => countRegistrarScans(function () use ($count): void {
        $permissions = PermissionSet::make();

        foreach (range(1, $count) as $index) {
            $permissions->allows('probe.many.' . $index);
        }
    });

    expect($ask(10))->toBe($ask(60));
});
