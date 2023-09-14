<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Currencies;
use FluxErp\Models\Currency;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class CurrenciesTest extends BaseSetup
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Currency::factory()->count(2)->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(Currencies::class)
            ->assertStatus(200);
    }
}
