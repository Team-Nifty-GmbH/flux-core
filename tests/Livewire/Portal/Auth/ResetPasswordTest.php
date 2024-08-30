<?php

namespace FluxErp\Tests\Livewire\Portal\Auth;

use FluxErp\Livewire\Portal\Auth\ResetPassword;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ResetPasswordTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ResetPassword::class)
            ->assertStatus(200);
    }
}
