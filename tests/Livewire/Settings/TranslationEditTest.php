<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\TranslationEdit;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TranslationEditTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(TranslationEdit::class)
            ->assertStatus(200);
    }
}
