<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Pivots\Categorizable;
use FluxErp\Models\Product;
use FluxErp\Models\Project;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CategorizableTableSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::query()->get('id');
        $cutCategoryIds = $categoryIds->random(max(1,bcfloor($categoryIds->count() * 0.8)));

        for ($i = 0; $i < 10; $i++) {
            $modelClass = Arr::random([
                Contact::class,
                Task::class,
                Product::class,
            ]);

            $idList = $modelClass::query()->pluck('id')->toArray();
            $instanceId = faker()->unique()->randomElement($idList);

            Categorizable::firstOrCreate([
                'categorizable_type' => $modelClass,
                'categorizable_id' => $instanceId,
                'category_id' => $cutCategoryIds->random()->getKey(),
            ]);
        }
    }
}
