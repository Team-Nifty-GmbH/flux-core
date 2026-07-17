<?php

use FluxErp\Livewire\Settings\OrderTypes;
use FluxErp\Models\OrderType;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderTypes::class)
        ->assertOk();
});

test('sort rows updates order', function (): void {
    $orderTypes = OrderType::factory()->count(3)->create();

    $last = $orderTypes->last();

    Livewire::test(OrderTypes::class)
        ->call('sortRows', $last->getKey(), 0)
        ->assertHasNoErrors();

    expect($last->fresh()->order_column)->toBe(1);
});
