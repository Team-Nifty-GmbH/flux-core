<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Favorite;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\Project;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class FavoriteTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $idList = User::query()->pluck('id')->toArray();
            $instanceId = faker()->unique()->randomElement($idList);

            Favorite::firstOrCreate(Favorite::factory()->make([
                'authenticatable_type' => User::class,
                'authenticatable_id' => $instanceId,
            ])->toArray());
        }
    }
}
