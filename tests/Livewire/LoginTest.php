<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Auth\Login;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class LoginTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Login::class)
            ->assertStatus(200);
    }
}
