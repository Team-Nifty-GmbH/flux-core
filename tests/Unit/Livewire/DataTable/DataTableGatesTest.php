<?php

use FluxErp\Actions\DataTable\ShareFilter;
use FluxErp\Tests\Unit\Livewire\DataTable\ExportTestDataTable;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use TeamNiftyGmbH\DataTable\Models\DatatableUserSetting;
use function Livewire\invade;

test('can save default columns when user has super admin role', function (): void {
    $role = Role::findOrCreate('Super Admin');
    $this->user->assignRole($role);

    $component = Livewire::test(ExportTestDataTable::class);
    $instance = invade($component->instance());

    expect($instance->canSaveDefaultColumns())->toBeTrue();
});

test('cannot save default columns when user has no super admin role', function (): void {
    $component = Livewire::test(ExportTestDataTable::class);
    $instance = invade($component->instance());

    expect($instance->canSaveDefaultColumns())->toBeFalse();
});

test('can share filters when user has action permission', function (): void {
    $permission = Permission::findOrCreate('action.' . ShareFilter::name());
    $this->user->givePermissionTo($permission);

    $component = Livewire::test(ExportTestDataTable::class);
    $instance = invade($component->instance());

    expect($instance->canShareFilters())->toBeTrue();
});

test('cannot share filters when user lacks action permission', function (): void {
    Permission::findOrCreate('action.' . ShareFilter::name());

    $component = Livewire::test(ExportTestDataTable::class);
    $instance = invade($component->instance());

    expect($instance->canShareFilters())->toBeFalse();
});

test('share filter action sets is_shared on datatable user setting', function (): void {
    $setting = DatatableUserSetting::create([
        'authenticatable_id' => $this->user->getKey(),
        'authenticatable_type' => $this->user->getMorphClass(),
        'name' => 'Test Filter',
        'component' => ExportTestDataTable::class,
        'cache_key' => ExportTestDataTable::class,
        'settings' => ['userFilters' => []],
        'is_shared' => false,
    ]);

    $result = ShareFilter::make([
        'id' => $setting->getKey(),
        'is_shared' => true,
    ])->validate()->execute();

    expect($result->is_shared)->toBeTrue();
});

test('share filter action can unshare a filter', function (): void {
    $setting = DatatableUserSetting::create([
        'authenticatable_id' => $this->user->getKey(),
        'authenticatable_type' => $this->user->getMorphClass(),
        'name' => 'Shared Filter',
        'component' => ExportTestDataTable::class,
        'cache_key' => ExportTestDataTable::class,
        'settings' => ['userFilters' => []],
        'is_shared' => true,
    ]);

    $result = ShareFilter::make([
        'id' => $setting->getKey(),
        'is_shared' => false,
    ])->validate()->execute();

    expect($result->is_shared)->toBeFalse();
});
