<?php

use FluxErp\Actions\Cart\CreateCart;
use FluxErp\Actions\CartItem\CreateCartItem;
use FluxErp\Actions\CartItem\DeleteCartItem;
use FluxErp\Actions\CartItem\UpdateCartItem;
use FluxErp\Models\CartItem;
use FluxErp\Models\PriceList;

beforeEach(function (): void {
    $this->cart = CreateCart::make([
        'price_list_id' => PriceList::factory()->create()->getKey(),
    ])->validate()->execute();
});

test('create cart item', function (): void {
    $item = CreateCartItem::make([
        'cart_id' => $this->cart->getKey(),
        'name' => 'Widget',
        'price' => 29.99,
        'vat_rate_id' => FluxErp\Models\VatRate::factory()->create()->getKey(),
    ])->validate()->execute();

    expect($item)->toBeInstanceOf(CartItem::class)
        ->name->toBe('Widget');
});

test('create cart item requires cart_id and price', function (): void {
    CreateCartItem::assertValidationErrors([], ['cart_id', 'price']);
});

test('update cart item', function (): void {
    $item = CreateCartItem::make([
        'cart_id' => $this->cart->getKey(),
        'name' => 'Original',
        'price' => 10.00,
        'vat_rate_id' => FluxErp\Models\VatRate::factory()->create()->getKey(),
    ])->validate()->execute();

    $updated = UpdateCartItem::make([
        'id' => $item->getKey(),
        'amount' => 5,
    ])->validate()->execute();

    expect($updated)->not->toBeNull();
});

test('delete cart item', function (): void {
    $item = CreateCartItem::make([
        'cart_id' => $this->cart->getKey(),
        'name' => 'Temp',
        'price' => 5.00,
        'vat_rate_id' => FluxErp\Models\VatRate::factory()->create()->getKey(),
    ])->validate()->execute();

    expect(DeleteCartItem::make(['id' => $item->getKey()])
        ->validate()->execute())->toBeTrue();
});
