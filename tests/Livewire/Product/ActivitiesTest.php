<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\Activities;
use FluxErp\Models\Product;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ActivitiesTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(
            Activities::class,
            [
                'modelId' => $this->product->id,
                'modelType' => $this->product->getMorphClass(),
            ]
        )
            ->assertStatus(200);
    }
}
