<?php

namespace FluxErp\Tests\Livewire\Features\Comments;

use FluxErp\Models\Product;
use FluxErp\Support\Livewire\Comments;
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

    public function test_renders_successfully(): void
    {
        Livewire::test(Comments::class, ['modelType' => Product::class, 'modelId' => $this->product->id])
            ->assertStatus(200);
    }
}
