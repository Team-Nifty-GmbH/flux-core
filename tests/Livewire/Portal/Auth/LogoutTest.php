<?php

namespace FluxErp\Tests\Livewire\Portal\Auth;

use FluxErp\Livewire\Portal\Auth\Logout;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LogoutTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Logout::class)
            ->assertStatus(200);
    }
}
