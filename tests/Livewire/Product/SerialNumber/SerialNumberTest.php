<?php

namespace FluxErp\Tests\Livewire\Product\SerialNumber;

use FluxErp\Livewire\Product\SerialNumber\SerialNumber as SerialNumberView;
use FluxErp\Models\SerialNumber;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class SerialNumberTest extends TestCase
{
    use DatabaseTransactions;

    private SerialNumber $serialNumber;

    public function setUp(): void
    {
        parent::setUp();

        $this->serialNumber = SerialNumber::factory()->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(SerialNumberView::class, ['id' => $this->serialNumber->id])
            ->assertStatus(200);
    }
}
