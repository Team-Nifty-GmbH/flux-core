<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Database;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Database::class)
        ->assertStatus(200);
});
