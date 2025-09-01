<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Storage;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Storage::class)
        ->assertStatus(200);
});
