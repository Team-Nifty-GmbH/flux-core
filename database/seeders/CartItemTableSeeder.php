<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use Illuminate\Database\Seeder;

class CartItemTableSeeder extends Seeder
{
    public function run(): void
    {
        $cartIds = Cart::query()->get('id')->random(rand(2, 5));
        $productIds = Product::query()->get('id')->random(rand(5, 15));
        $vatRateIds = VatRate::query()->get('id')->random(rand(2, 3));

        foreach ($cartIds as $cartId) {
            foreach ($productIds as $productId) {
                foreach ($vatRateIds as $vatRateId) {
                    CartItem::factory()->create([
                        'cart_id' => $cartId->getKey(),
                        'product_id' => $productId->getKey(),
                        'vat_rate_id' => $vatRateId->getKey(),
                    ]);
                }
            }
        }
    }
}
