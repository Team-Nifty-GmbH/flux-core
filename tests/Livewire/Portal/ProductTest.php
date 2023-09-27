<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ProductTest extends BaseSetup
{
    use DatabaseTransactions;

    private SerialNumber $serialNumber;

    public function setUp(): void
    {
        parent::setUp();

        $this->serialNumber = SerialNumber::factory()->create(['address_id' => $this->address->id]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Product::class, ['id' => $this->serialNumber->id])
            ->assertStatus(200);
    }
}
