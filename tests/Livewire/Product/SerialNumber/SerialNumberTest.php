<?php

namespace FluxErp\Tests\Livewire\Product\SerialNumber;

use FluxErp\Livewire\Product\SerialNumber\SerialNumber;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class SerialNumberTest extends BaseSetup
{
    use DatabaseTransactions;

    private \FluxErp\Models\SerialNumber $serialNumber;

    public function setUp(): void
    {
        parent::setUp();

        $this->serialNumber = \FluxErp\Models\SerialNumber::factory()->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(SerialNumber::class, ['id' => $this->serialNumber->id])
            ->assertStatus(200);
    }
}
