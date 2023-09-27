<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\PriceLists;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class PriceListsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(PriceLists::class)
            ->assertStatus(200);
    }
}
