<?php

namespace Tests\Feature\Livewire\Address;

use FluxErp\Livewire\Address\Comments;
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
