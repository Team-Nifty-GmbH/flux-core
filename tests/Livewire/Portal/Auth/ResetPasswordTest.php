<?php

namespace Tests\Feature\Livewire\Portal\Auth;

use FluxErp\Livewire\Portal\Auth\ResetPassword;
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
