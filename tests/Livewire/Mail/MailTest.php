<?php

namespace Tests\Feature\Livewire\Mail;

use FluxErp\Livewire\Mail\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MailTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Mail::class)
            ->assertStatus(200);
    }
}
