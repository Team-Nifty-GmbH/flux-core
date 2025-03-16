<?php

namespace FluxErp\Tests\Livewire\Address;

use FluxErp\Livewire\Address\Tasks;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TasksTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);
        $address = Address::factory()->create([
            'contact_id' => $contact->id,
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $this->user->language_id,
            'can_login' => false,
            'is_active' => true,
        ]);

        Livewire::test(Tasks::class, ['modelId' => $address->id])
            ->assertStatus(200);
    }
}
