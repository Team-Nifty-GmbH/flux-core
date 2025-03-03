<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Users;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class UsersTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Users::class)
            ->assertStatus(200);
    }
}
