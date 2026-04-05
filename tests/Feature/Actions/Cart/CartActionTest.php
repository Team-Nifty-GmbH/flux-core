<?php

use FluxErp\Actions\Cart\CreateCart;
use FluxErp\Actions\Cart\DeleteCart;
use FluxErp\Actions\Cart\UpdateCart;
use FluxErp\Models\Cart;
use FluxErp\Models\PriceList;

test('create cart', function (): void {
    $cart = CreateCart::make([
        'price_list_id' => PriceList::factory()->create()->getKey(),
    ])->validate()->execute();

    expect($cart)->toBeInstanceOf(Cart::class);
});

test('update cart', function (): void {
    $cart = CreateCart::make([
        'price_list_id' => PriceList::factory()->create()->getKey(),
    ])->validate()->execute();

    $updated = UpdateCart::make([
        'id' => $cart->getKey(),
        'name' => 'My Wishlist',
    ])->validate()->execute();

    expect($updated->name)->toBe('My Wishlist');
});

test('delete cart', function (): void {
    $cart = CreateCart::make([
        'price_list_id' => PriceList::factory()->create()->getKey(),
    ])->validate()->execute();

    expect(DeleteCart::make(['id' => $cart->getKey()])
        ->validate()->execute())->toBeTrue();
});
