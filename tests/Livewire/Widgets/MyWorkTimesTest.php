<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\MyWorkTimes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MyWorkTimes::class)
        ->assertStatus(200);
});
