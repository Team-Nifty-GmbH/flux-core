<?php

use FluxErp\Livewire\Product\Activities;
use FluxErp\Models\Product;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $product = Product::factory()->create();

    Livewire::test(
        Activities::class,
        [
            'modelId' => $product->id,
            'modelType' => $product->getMorphClass(),
        ]
    )
        ->assertOk();
});
