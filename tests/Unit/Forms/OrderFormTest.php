<?php

use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Settings\Units;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

test('getPriceList falls back to the default price list for an unknown price list id', function (): void {
    $form = new OrderForm(Livewire::test(Units::class)->instance(), 'order');
    $form->price_list_id = null;

    expect($form->getPriceList())
        ->toBeInstanceOf(PriceList::class)
        ->getKey()->toBe(PriceList::default()->getKey());
});

test('getPriceList returns the price list of the form', function (): void {
    $priceList = PriceList::factory()->create();

    $form = new OrderForm(Livewire::test(Units::class)->instance(), 'order');
    $form->price_list_id = $priceList->getKey();

    expect($form->getPriceList()->getKey())->toBe($priceList->getKey());
});
