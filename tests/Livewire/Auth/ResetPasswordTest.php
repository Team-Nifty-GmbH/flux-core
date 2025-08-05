<?php

namespace FluxErp\Tests\Livewire\Auth;

use FluxErp\Livewire\Auth\ResetPassword;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ResetPasswordTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(ResetPassword::class)
            ->assertStatus(200);
    }
}
