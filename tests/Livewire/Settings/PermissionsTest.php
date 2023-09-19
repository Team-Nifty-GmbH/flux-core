<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Permissions;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PermissionsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Permissions::class)
            ->assertStatus(200);
    }
}
