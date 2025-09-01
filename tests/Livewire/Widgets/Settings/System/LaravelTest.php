<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Laravel;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Laravel::class)
        ->assertStatus(200);
});
