<?php

namespace FluxErp\Tests\Livewire\Ticket;

use FluxErp\Livewire\Ticket\Comments;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CommentsTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::actingAs($this->user)
            ->test(Comments::class)
            ->assertStatus(200);
    }
}
