<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Projects;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ProjectsTest extends BaseSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Projects::class, ['contactId' => $this->contact->id])
            ->assertStatus(200);
    }
}
