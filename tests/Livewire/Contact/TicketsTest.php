<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Tickets;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TicketsTest extends BaseSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(Tickets::class, ['contactId' => $this->contact->id])
            ->assertStatus(200);
    }
}
