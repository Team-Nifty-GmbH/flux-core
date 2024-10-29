<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\TicketTypes;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\TicketType;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TicketTypesTest extends BaseSetup
{
    public function setUp(): void
    {
        parent::setUp();

        TicketType::factory()
            ->has(AdditionalColumn::factory()->count(3))
            ->count(5)
            ->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(TicketTypes::class)
            ->assertStatus(200);
    }
}
