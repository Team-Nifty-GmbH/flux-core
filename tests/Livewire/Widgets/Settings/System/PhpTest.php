<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Php;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Php::class)
        ->assertStatus(200);
});
