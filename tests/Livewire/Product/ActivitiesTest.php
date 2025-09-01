<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Product\Activities;
use FluxErp\Models\Product;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->product = Product::factory()->create();
});

test('renders successfully', function (): void {
    Livewire::test(
        Activities::class,
        [
            'modelId' => $this->product->id,
            'modelType' => $this->product->getMorphClass(),
        ]
    )
        ->assertStatus(200);
});
