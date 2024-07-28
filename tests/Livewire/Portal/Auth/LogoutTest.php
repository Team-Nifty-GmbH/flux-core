<?php

namespace Tests\Feature\Livewire\Portal\Auth;

use FluxErp\Livewire\Portal\Auth\Logout;
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
