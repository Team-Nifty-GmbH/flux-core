<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\AdditionalColumnEdit;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AdditionalColumnEditTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(AdditionalColumnEdit::class)
            ->assertStatus(200);
    }
}
