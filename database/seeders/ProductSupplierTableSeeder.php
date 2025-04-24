<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use Illuminate\Database\Seeder;

class ProductSupplierTableSeeder extends Seeder
{
    public function run(): void
    {
        $productIds = Product::query()->get('id');
        $cutProductIds = $productIds->random(bcfloor($productIds->count() * 0.6));

        $ContactIds = Contact::query()->get('id');
        $cutContactIds = $ContactIds->random(bcfloor($ContactIds->count() * 0.6));

        foreach ($cutProductIds as $cutProductId) {
            $cutProductId->suppliers()->attach(
                $cutContactIds->random(rand(1, $cutContactIds->count())),
                [
                    'manufacturer_product_number' => faker()->ean8(),
                    'purchase_price' => faker()->randomFloat(2, 1, 1000),
                ]
            );
        }
    }
}
