<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Tokens;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TokensTest extends TestCase
{
    protected string $livewireComponent = Tokens::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
