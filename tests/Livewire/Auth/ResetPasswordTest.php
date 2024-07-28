<?php

namespace Tests\Feature\Livewire\Auth;

use FluxErp\Livewire\Auth\ResetPassword;
use Livewire\Livewire;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ResetPassword::class)
            ->assertStatus(200);
    }
}
