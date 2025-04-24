<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Category;
use FluxErp\Models\Discount;
use FluxErp\Models\PriceList;
use Illuminate\Database\Seeder;

class CategoryPriceListTableSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::query()->get('id');
        $cutCategoryIds = $categoryIds->random(bcfloor($categoryIds->count() * 0.6));

        $priceListIds = PriceList::query()->get('id');
        $cutPriceListIds = $priceListIds->random(bcfloor($priceListIds->count() * 0.6));

        $discountIds = Discount::query()->get('id');
        $cutDiscountIds = $discountIds->random(bcfloor($discountIds->count() * 0.6));

        foreach ($cutCategoryIds as $categoryId) {
            $categoryId->priceLists()->attach($cutPriceListIds->random(
                rand(1, max(1,bcfloor($cutPriceListIds->count() * 0.2)))
            ), [
                'discount_id' => $cutDiscountIds->random()->getKey(),
            ]);
        }
    }
}
