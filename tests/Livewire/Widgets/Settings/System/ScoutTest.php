<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Scout;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Scout::class)
        ->assertStatus(200);
});
