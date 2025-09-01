<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\RecordMerging;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecordMerging::class)
        ->assertStatus(200);
});
