<?php

namespace FluxErp\Tests\Livewire\Features\Comments;

use FluxErp\Livewire\Features\Comments\Comments;
use FluxErp\Models\Product;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CommentsTest extends BaseSetup
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(Comments::class, ['modelType' => Product::class, 'modelId' => $this->product->id])
            ->assertStatus(200);
    }
}
