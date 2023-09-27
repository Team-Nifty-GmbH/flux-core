<?php

namespace FluxErp\Tests\Livewire\Features\Comments;

use FluxErp\Livewire\Features\Comments\Comments;
use FluxErp\Models\Product;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class CommentsTest extends BaseSetup
{
    use DatabaseTransactions;

    private Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Comments::class, ['modelType' => Product::class, 'modelId' => $this->product->id])
            ->assertStatus(200);
    }
}
