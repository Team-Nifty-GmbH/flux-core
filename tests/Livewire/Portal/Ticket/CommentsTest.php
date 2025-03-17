<?php

namespace FluxErp\Tests\Livewire\Portal\Ticket;

use FluxErp\Livewire\Portal\Ticket\Comments;
use FluxErp\Models\Ticket;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CommentsTest extends BaseSetup
{
    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ticket = Ticket::factory()->create([
            'authenticatable_type' => $this->address->getMorphClass(),
            'authenticatable_id' => $this->address->id,
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(Comments::class, ['modelId' => $this->ticket->id])
            ->assertStatus(200);
    }
}
