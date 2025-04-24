<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\Project;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AdditionalColumnTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 33; $i++) {
            $modelClass = Arr::random([
                Contact::class,
                Task::class,
                Project::class,
                OrderPosition::class,
                Order::class,
                SerialNumber::class,
                Category::class,
                Product::class,
                Ticket::class,
                Address::class,
                TicketType::class,
            ]);

            $idList = $modelClass::query()->pluck('id')->toArray();
            $instanceId = faker()->unique()->randomElement($idList);

            AdditionalColumn::firstOrCreate(AdditionalColumn::factory()->make([
                'model_type' => $modelClass,
                'model_id' => $instanceId,
            ])->toArray());
        }
    }
}
