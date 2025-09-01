<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Facades\Menu;
use FluxErp\Livewire\Navigation;
use FluxErp\Models\OrderType;
use FluxErp\Models\PriceList;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    PriceList::factory()->create(['is_default' => true]);
});

test('renders successfully', function (): void {
    Livewire::test(Navigation::class)
        ->assertStatus(200);
});

test('shows order types', function (): void {
    $orderTypes = OrderType::factory(5)
        ->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
            'is_visible_in_sidebar' => true,
        ]);

    Livewire::actingAs($this->user)
        ->test(Navigation::class)
        ->assertSee($orderTypes->map(fn ($orderType) => Str::headline($orderType->name))->toArray());

    $orderTypes->first()->update(['is_visible_in_sidebar' => false]);
    Menu::clear();

    Livewire::actingAs($this->user)
        ->test(Navigation::class)
        ->assertDontSee(Str::headline($orderTypes->first()->name));
});
