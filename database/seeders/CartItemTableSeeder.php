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
        $cartIds = Cart::query()->get('id');
        $cutCartIds = $cartIds->random(bcfloor($cartIds->count() * 0.7));

        $productIds = Product::query()->get('id');
        $cutProductIds = $productIds->random(bcfloor($productIds->count() * 0.7));

        $vatRateIds = VatRate::query()->get('id');
        $cutVatRateIds = $vatRateIds->random(bcfloor($vatRateIds->count() * 0.7));

        CartItem::factory()->count(30)->create([
            'cart_id' => fn () => $cutCartIds->random()->getKey(),
            'product_id' => fn () => faker()->boolean() ? $cutProductIds->random()->getKey() : null,
            'vat_rate_id' => fn () => $cutVatRateIds->random()->getKey(),
        ]);
    }
}
