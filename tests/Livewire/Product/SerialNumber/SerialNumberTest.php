<?php

namespace FluxErp\Tests\Livewire\Product\SerialNumber;

use FluxErp\Livewire\Product\SerialNumber\SerialNumber as SerialNumberView;
use FluxErp\Models\Client;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SerialNumberTest extends TestCase
{
    private SerialNumber $serialNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()
            ->hasAttached(factory: $dbClient, relationship: 'clients')
            ->create();

        $this->serialNumber = SerialNumber::factory()->create();

        StockPosting::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'serial_number_id' => $this->serialNumber->id,
            'posting' => 1,
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(SerialNumberView::class, ['id' => $this->serialNumber->id])
            ->assertStatus(200);
    }
}
