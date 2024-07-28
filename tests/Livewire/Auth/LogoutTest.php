<?php

namespace Tests\Feature\Livewire\Auth;

use FluxErp\Livewire\Auth\Logout;
use Livewire\Livewire;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Logout::class)
            ->assertStatus(200);
    }
}
