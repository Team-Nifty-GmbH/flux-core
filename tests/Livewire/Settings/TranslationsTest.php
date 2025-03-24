<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\LanguageLines;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TranslationsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(LanguageLines::class)
            ->assertStatus(200);
    }
}
