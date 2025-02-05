<?php

namespace FluxErp\Tests\Livewire\Address;

use FluxErp\Livewire\Address\Comments;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CommentsTest extends BaseSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $this->address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::actingAs($this->user)
            ->test(Comments::class, ['modelId' => $this->address->id])
            ->assertStatus(200);
    }
}
