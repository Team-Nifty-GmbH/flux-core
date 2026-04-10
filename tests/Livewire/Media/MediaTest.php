<?php

use FluxErp\Livewire\Media\Media;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Media::class)
        ->assertOk();
});

test('url column is always present after loadData', function (): void {
    $component = Livewire::test(Media::class)
        ->call('loadData');

    expect($component->get('enabledCols'))->toContain('url');
});
