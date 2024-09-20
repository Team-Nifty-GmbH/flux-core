<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    public function run(): void
    {
        $units = Unit::all(['id']);
        $vatRates = VatRate::all(['id']);
        $clients = Client::all(['id']);
        $products = Product::all(['id']);
        $priceLists = PriceList::query()
            ->whereNull('parent_id')
            ->get(['id']);

        $createdProducts = collect();
        for ($i = 0; $i < 20; $i++) {
            $createdProducts->push(Product::factory()->create([
                'unit_id' => $units->random()?->id,
                'client_id' => $clients->random()?->id,
                'purchase_unit_id' => $units->random()?->id,
                'reference_unit_id' => $units->random()?->id,
                'vat_rate_id' => $vatRates->random()?->id,
                'parent_id' => rand(0, 1) ? ($products->isEmpty() ? null : $products->random()->id) : null,
            ]));
        }

        $productOptions = ProductOption::all(['id']);
        $productProperties = ProductProperty::all(['id']);
        $products = Product::query()
            ->where('is_bundle', false)
            ->get();

        foreach ($createdProducts as $product) {
            $product->productOptions()->sync($productOptions->random(rand(5, count($productOptions))));
            $product->productProperties()
                ->syncWithPivotValues(
                    $productProperties->random(rand(5, count($productProperties))),
                    ['value' => rand(5, 100)]
                );

            $product->addMediaFromUrl('https://picsum.photos/seed/' . $product->id . '/200/300')
                ->toMediaCollection('images');

            if ($product->is_bundle && $products->count()) {
                $product->bundleProducts()
                    ->sync(
                        $products->random(rand(1, $products->count()))
                            ->mapWithKeys(fn ($item) => [$item['id'] => ['count' => rand(1, 100)]])
                            ->toArray()
                    );
            }

            foreach ($priceLists as $priceList) {
                Price::factory()->create([
                    'product_id' => $product->id,
                    'price_list_id' => $priceList->id,
                ]);
            }
        }
    }
}
