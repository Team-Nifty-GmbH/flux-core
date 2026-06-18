<?php

use FluxErp\Livewire\Widgets\SearchBar;
use FluxErp\Models\Order;
use FluxErp\Models\Permission;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SearchBar::class)
        ->assertOk();
});

test('renderSearchBarWidget refuses models the user has no detail route permission for', function (): void {
    Permission::findOrCreate('orders.{id}.get', 'web');

    Livewire::test(SearchBar::class)
        ->dispatch('renderSearchBarWidget', model: Order::class, modelId: 1)
        ->assertSet('show', false)
        ->assertSet('widgetComponent', null);
});

test('renderSearchBarWidget does not show a widget for a model that no longer exists', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'web'));

    Livewire::test(SearchBar::class)
        ->dispatch('renderSearchBarWidget', model: Order::class, modelId: 999999999)
        ->assertSet('show', false)
        ->assertSet('widgetComponent', null);
});
