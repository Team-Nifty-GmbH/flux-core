<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\AttributeTranslationList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AttributeTranslationList::class)
        ->assertStatus(200);
});
