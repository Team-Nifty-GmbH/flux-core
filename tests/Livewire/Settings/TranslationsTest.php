<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\LanguageLines;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LanguageLines::class)
        ->assertStatus(200);
});
