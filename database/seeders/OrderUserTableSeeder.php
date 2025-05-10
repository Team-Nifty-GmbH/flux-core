<?php

namespace FluxErp\Database\Seeders;

use App\Models\User;
use FluxErp\Models\Order;
use FluxErp\Models\Pivots\OrderUser;
use Illuminate\Database\Seeder;

class OrderUserTableSeeder extends Seeder
{
    public function run(): void
    {
        $orderIds = Order::query()->get('id');
        $cutOrderIds = $orderIds->random(bcfloor($orderIds->count() * 0.7));

        $userIds = User::query()->get('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.7));

        foreach ($cutOrderIds as $cutOrderId) {
            $numGroups = rand(1, floor($cutUserIds->count() * 0.3));

            $ids = $cutUserIds->random($numGroups)->pluck('id')->toArray();

            foreach ($ids as $id) {
                OrderUser::factory()->create([
                    'order_id' => $cutOrderId,
                    'user_id' => $id,
                ]);
            }
        }
    }
}
