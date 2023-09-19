<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\ClientEdit;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ClientEditTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ClientEdit::class)
            ->assertStatus(200);
    }
}
