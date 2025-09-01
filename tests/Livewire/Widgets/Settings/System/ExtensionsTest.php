<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Extensions;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Extensions::class)
        ->assertStatus(200);
});
