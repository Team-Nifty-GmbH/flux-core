<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Cache;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Cache::class)
        ->assertStatus(200);
});
