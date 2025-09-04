<?php

use FluxErp\Livewire\Forms\PriceListForm;
use FluxErp\Livewire\Settings\PriceLists;
use FluxErp\Models\PriceList;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->priceList = PriceList::factory()->create();
});

test('can create price list', function (): void {
    Livewire::test(PriceLists::class)
        ->assertOk()
        ->assertHasNoErrors()
        ->set('priceList.name', $name = Str::uuid())
        ->set('priceList.price_list_code', Str::uuid())
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('price_lists', [
        'name' => $name,
    ]);
});

test('can update price list', function (): void {
    $child = PriceList::factory()
        ->create([
            'parent_id' => $this->priceList->id,
        ]);
    $form = new PriceListForm(Livewire::new(PriceLists::class), 'contact');
    $form->fill($child);

    Livewire::test(PriceLists::class, ['priceList' => $form])
        ->assertOk()
        ->assertHasNoErrors()
        ->set('priceList.name', 'New Name')
        ->set(
            'priceList.discount',
            [
                'discount' => 10,
                'is_percentage' => true,
            ]
        )
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $dbPriceList = $child->refresh();

    expect($dbPriceList->name)->toEqual('New Name');
    expect($dbPriceList->discount->discount)->toEqual(0.1);
    expect($dbPriceList->discount->is_percentage)->toBeTrue();
});

test('renders successfully', function (): void {
    Livewire::test(PriceLists::class)
        ->assertOk();
});
