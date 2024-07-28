<?php

namespace Tests\Feature\Livewire\Contact;

use FluxErp\Livewire\Contact\Contact;
use Livewire\Livewire;
use Tests\TestCase;

class ContactTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Contact::class)
            ->assertStatus(200);
    }
}
