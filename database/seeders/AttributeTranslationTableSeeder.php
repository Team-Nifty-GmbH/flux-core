<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\AttributeTranslation;
use FluxErp\Models\Category;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductProperty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AttributeTranslationTableSeeder extends Seeder
{
    public function run(): void
    {
        $languageIds = Language::query()->get('id');
        $cutLanguageIds = $languageIds->random(bcfloor($languageIds->count() * 0.8));

        for ($i = 0; $i < 30; $i++) {
            $modelClass = Arr::random([
                PaymentType::class,
                PaymentReminderText::class,
                ProductProperty::class,
                ProductOption::class,
                Category::class,
                Product::class,
            ]);

            $idList = $modelClass::query()->pluck('id')->toArray();
            $instanceId = faker()->unique()->randomElement($idList);

            AttributeTranslation::firstOrCreate(AttributeTranslation::factory()->make([
                'language_id' => $cutLanguageIds->random()->getKey(),
                'model_type' => $modelClass,
                'model_id' => $instanceId,
            ])->toArray());
        }
    }
}
