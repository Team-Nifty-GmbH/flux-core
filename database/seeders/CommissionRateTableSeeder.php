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
        $userIds = User::query()->get('id')->random(rand(5, 9));
        $contactIds = Contact::query()->get('id');
        $categoryIds = Category::query()->get('id');
        $productIds = Product::query()->get('id');

        $userIds->each(function ($userId) use ($contactIds, $categoryIds, $productIds): void {
            $user = data_get(User::query()->find($userId), 0);

            $commissionRates = [];
            $count = rand(1, 3);

            for ($i = 0; $i < $count; $i++) {
                $parameters = [];

                if (rand(0, 1) === 1) {
                    $parameters[] = ['contact_id' => $contactIds->random()->getKey()];
                }
                if (rand(0, 1) === 1) {
                    $parameters[] = ['category_id' => $categoryIds->random()->getKey()];
                }
                if (rand(0, 1) === 1) {
                    $parameters[] = ['product_id' => $productIds->random()->getKey()];
                }

                $parameters[] = ['user_id' => $user->getKey()];

                $parameters = array_merge(...$parameters);

                $commissionRates[] = CommissionRate::factory()->make($parameters)->toArray();
            }
            $user->commissionRates()->createMany($commissionRates);
        });
    }
}
