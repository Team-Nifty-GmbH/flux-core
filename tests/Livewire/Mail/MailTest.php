<?php

namespace FluxErp\Tests\Livewire\Mail;

use FluxErp\Livewire\Mail\Mail;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class MailTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::actingAs($this->user)
            ->test(Mail::class)
            ->assertStatus(200);
    }
}
