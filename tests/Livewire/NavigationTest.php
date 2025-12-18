<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Facades\Menu;
use FluxErp\Livewire\Navigation;
use FluxErp\Models\OrderType;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Navigation::class)
        ->assertOk();
});

test('shows order types', function (): void {
    $orderTypes = OrderType::factory(5)
        ->create([
            'tenant_id' => $this->dbTenant->getKey(),
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
