<?php

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Models\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Exceptions\UnauthorizedException;

beforeEach(function (): void {
    Auth::login($this->user);
});

test('allows an action whose permission was never created', function (): void {
    Permission::query()->where('name', 'action.' . CreateContact::name())->delete();
    app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    expect(CreateContact::canPerformAction(false))->toBeTrue();
});

test('denies an action whose permission the user does not hold', function (): void {
    Permission::findOrCreate('action.' . CreateContact::name(), 'web');
    $this->user->revokePermissionTo('action.' . CreateContact::name());

    expect(CreateContact::canPerformAction(false))->toBeFalse();
});

test('throws for an action the user may not perform when asked to', function (): void {
    Permission::findOrCreate('action.' . CreateContact::name(), 'web');
    $this->user->revokePermissionTo('action.' . CreateContact::name());

    CreateContact::canPerformAction();
})->throws(UnauthorizedException::class);

test('allows an action whose permission the user holds', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('action.' . CreateContact::name(), 'web'));

    expect(CreateContact::canPerformAction(false))->toBeTrue();
});

test('allows an action when a gate grants every ability', function (): void {
    Permission::findOrCreate('action.' . CreateContact::name(), 'web');
    $this->user->revokePermissionTo('action.' . CreateContact::name());

    Gate::before(fn (Authenticatable $user, string $ability): ?true => true);

    expect(CreateContact::canPerformAction(false))->toBeTrue();
});

/**
 * Asking whether the permission exists and asking whether the user holds it both
 * walk every stored permission. Whoever holds it needs only the second question
 * answered, so an allowed check must not pay for the first one.
 */
test('asks the permission store once when the action is allowed', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('action.' . CreateContact::name(), 'web'));

    $scans = countRegistrarScans(fn () => CreateContact::canPerformAction(false));

    expect($scans)->toBeLessThanOrEqual(1);
});
