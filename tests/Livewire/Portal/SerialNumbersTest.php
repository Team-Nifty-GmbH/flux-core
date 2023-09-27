<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\SerialNumbers;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class SerialNumbersTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(SerialNumbers::class)
            ->assertStatus(200);
    }
}
