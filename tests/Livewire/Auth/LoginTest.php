<?php

namespace FluxErp\Tests\Livewire\Auth;

use FluxErp\Livewire\Auth\Login;
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
