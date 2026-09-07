<?php

use FluxErp\Console\Commands\Init\InitPermissions;
use FluxErp\Models\Permission;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

test('creates a permission for every action, model, route, widget, tab and print view', function (): void {
    Artisan::call(InitPermissions::class);

    $permissions = Permission::query()->get(['name', 'guard_name']);
    $prefixed = ['action', 'model', 'print', 'tab', 'widget'];
    $prefixes = $permissions->pluck('name')->map(fn (string $name): string => strtok($name, '.'))->unique();

    expect($prefixes->intersect($prefixed)->sort()->values()->all())->toBe($prefixed)
        ->and($prefixes->diff($prefixed)->all())->not->toBe([])
        ->and($permissions->pluck('guard_name')->unique()->sort()->values()->all())->toBe(['sanctum', 'token', 'web'])
        ->and($permissions->where('name', 'action.order.create')->pluck('guard_name')->sort()->values()->all())
        ->toBe(['sanctum', 'web'])
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
