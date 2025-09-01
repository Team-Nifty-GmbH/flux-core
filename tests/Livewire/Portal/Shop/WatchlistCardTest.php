<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Portal\Shop\WatchlistCard;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WatchlistCard::class)
        ->assertStatus(200);
});
