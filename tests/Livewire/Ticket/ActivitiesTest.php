<?php

namespace FluxErp\Tests\Livewire\Ticket;

use FluxErp\Livewire\Ticket\Activities;
use FluxErp\Models\Ticket;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ActivitiesTest extends BaseSetup
{
    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ticket = Ticket::factory()->create([
            'authenticatable_id' => $this->user->id,
            'authenticatable_type' => $this->user->getMorphClass(),
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(Activities::class, ['modelId' => $this->ticket->id])
            ->assertStatus(200);
    }
}
