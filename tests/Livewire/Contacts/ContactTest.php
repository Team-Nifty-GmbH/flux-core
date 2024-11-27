<?php

namespace FluxErp\Tests\Livewire\Contacts;

use FluxErp\Livewire\Contact\Contact as ContactView;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ContactTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $this->contact = Contact::factory()->create([
            'client_id' => $dbClient->id,
        ]);

        Address::factory()->create([
            'client_id' => $dbClient->id,
            'contact_id' => $this->contact->id,
            'is_main_address' => true,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(ContactView::class, ['id' => $this->contact->id])
            ->assertStatus(200);
    }
}
