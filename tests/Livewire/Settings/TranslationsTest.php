<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Translations;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TranslationsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Translations::class)
            ->assertStatus(200);
    }
}
