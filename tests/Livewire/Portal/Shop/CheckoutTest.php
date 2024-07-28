<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Checkout;
use FluxErp\Models\Currency;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CheckoutTest extends BaseSetup
{
    public function setUp(): void
    {
        parent::setUp();

        PriceList::factory()->create([
            'is_default' => true,
        ]);
        Currency::factory()->create([
            'is_default' => true,
        ]);
    }
    public function test_renders_successfully()
    {
        Livewire::actingAs($this->address)
            ->test(Checkout::class)
            ->assertStatus(200);
    }
}
