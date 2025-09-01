<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\LanguageLineList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LanguageLineList::class)
        ->assertStatus(200);
});
