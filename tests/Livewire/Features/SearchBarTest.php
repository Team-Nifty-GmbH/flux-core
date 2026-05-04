<?php

use FluxErp\Livewire\Features\SearchBar;
use FluxErp\Models\Order;
use FluxErp\Models\Permission;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SearchBar::class)
        ->assertOk();
});

test('searchable models exclude detail routes the user lacks permission for', function (): void {
    Permission::findOrCreate('orders.{id}.get', 'web');

    $component = Livewire::test(SearchBar::class)
        ->assertOk();

    expect($component->get('searchModel'))->not->toContain(Order::class);
    expect($component->get('modelLabels'))->not->toHaveKey(Order::class);
});

test('searchable models include detail routes the user has permission for', function (): void {
    $permission = Permission::findOrCreate('orders.{id}.get', 'web');
    $this->user->givePermissionTo($permission);

    $component = Livewire::test(SearchBar::class)
        ->assertOk();

    expect($component->get('searchModel'))->toContain(Order::class);
    expect($component->get('modelLabels'))->toHaveKey(Order::class);
});

test('showDetail rejects models the user has no detail route permission for', function (): void {
    Permission::findOrCreate('orders.{id}.get', 'web');

    Livewire::test(SearchBar::class)
        ->call('showDetail', Order::class, 1)
        ->assertNoRedirect()
        ->assertToastNotification(type: 'error');
});
