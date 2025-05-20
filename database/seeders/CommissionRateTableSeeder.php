<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Category;
use FluxErp\Models\CommissionRate;
use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class CommissionRateTableSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::query()->get('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.7));

        $contactIds = Contact::query()->get('id');
        $cutContactIds = $contactIds->random(bcfloor($contactIds->count() * 0.7));

        $categoryIds = Category::query()->get('id');
        $cutCategoryIds = $categoryIds->random(bcfloor($categoryIds->count() * 0.7));

        $productIds = Product::query()->get('id');
        $cutProduktIds = $productIds->random(bcfloor($productIds->count() * 0.7));

        foreach ($cutUserIds as $userId) {
            CommissionRate::factory()->count(rand(1, 3))->create([
                'user_id' => $userId,
                'contact_id' => fn () => faker()->boolean() ? $cutContactIds->random()->getKey() : null,
                'category_id' => fn () => faker()->boolean() ? $cutCategoryIds->random()->getKey() : null,
                'product_id' => fn () => faker()->boolean() ? $cutProduktIds->random()->getKey() : null,
            ]);
        }
    }
}
