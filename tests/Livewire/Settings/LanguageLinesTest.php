<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\LanguageLines;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LanguageLinesTest extends TestCase
{
    protected string $livewireComponent = LanguageLines::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
