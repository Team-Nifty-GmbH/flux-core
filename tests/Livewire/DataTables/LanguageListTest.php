<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\DataTables\LanguageList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LanguageList::class)
        ->assertStatus(200);
});
