<?php

namespace Tests\Feature\Livewire\Ticket;

use FluxErp\Livewire\Ticket\Comments;
use Livewire\Livewire;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Comments::class)
            ->assertStatus(200);
    }
}
