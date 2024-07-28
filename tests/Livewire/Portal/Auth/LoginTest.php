<?php

namespace Tests\Feature\Livewire\Portal\Auth;

use FluxErp\Livewire\Portal\Auth\Login;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Login::class)
            ->assertStatus(200);
    }
}
