<?php

namespace FluxErp\Tests\Livewire\Portal\Auth;

use FluxErp\Livewire\Portal\Auth\Login;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LoginTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Login::class)
            ->assertStatus(200);
    }
}
