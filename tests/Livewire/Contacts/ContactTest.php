<?php

namespace FluxErp\Tests\Livewire\Contacts;

use FluxErp\Livewire\Contacts\Contact;
use FluxErp\Models\Address;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ContactTest extends BaseSetup
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->contact = \FluxErp\Models\Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        Address::factory()->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $this->contact->id,
            'is_main_address' => true,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Contact::class, ['id' => $this->contact->id])
            ->assertStatus(200);
    }
}
