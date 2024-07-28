<?php

namespace FluxErp\Tests\Livewire\Address;

use FluxErp\Livewire\Address\Comments;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CommentsTest extends BaseSetup
{
    private Address $addressModel;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::factory()->create([
            'is_default' => true,
        ]);
        $contact = Contact::factory()->create([
            'client_id' => $client->id,
        ]);

        $this->addressModel = Address::factory()->create([
            'client_id' => $client->id,
            'contact_id' => $contact->id,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::actingAs($this->user)
            ->test(Comments::class, ['modelId' => $this->addressModel->id])
            ->assertStatus(200);
    }
}
