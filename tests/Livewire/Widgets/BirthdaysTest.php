<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\Birthdays;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class BirthdaysTest extends BaseSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $this->address = Address::factory()->create([
            'contact_id' => $this->contact->id,
            'client_id' => $this->dbClient->getKey(),
            'date_of_birth' => now()->subYears(30),
            'is_active' => true,
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(Birthdays::class)
            ->assertStatus(200)
            ->assertCount('items', 1)
            ->assertSet('items.0.label', $this->address->name)
            ->assertSet('items.0.subLabel', $this->address->date_of_birth->isoFormat('L') . ' (30)');
    }
}
