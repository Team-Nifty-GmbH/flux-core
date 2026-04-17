<?php

use FluxErp\Livewire\Settings\Industries;
use FluxErp\Models\Industry;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Industries::class)
        ->assertOk();
});

test('sort rows updates order', function (): void {
    $industries = Industry::factory()->count(3)->create();

    $last = $industries->last();

    Livewire::test(Industries::class)
        ->call('sortRows', $last->getKey(), 0)
        ->assertHasNoErrors();

    expect($last->fresh()->order_column)->toBe(1);
});
