<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\RecentActivities;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecentActivities::class)
        ->assertStatus(200);
});
