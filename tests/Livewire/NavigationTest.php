<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Navigation;
use FluxErp\Models\PriceList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class NavigationTest extends BaseSetup
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        PriceList::factory()->create(['is_default' => true]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Navigation::class)
            ->assertStatus(200);
    }
}
