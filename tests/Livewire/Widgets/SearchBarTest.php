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
