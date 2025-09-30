<?php

use FluxErp\Livewire\Settings\LanguageLines;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LanguageLines::class)
        ->assertOk();
});
