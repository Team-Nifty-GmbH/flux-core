<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\ContactForm;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ContactForm::class)
            ->assertStatus(200);
    }
}
