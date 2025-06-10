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
        $orderIds = Order::query()->pluck('id');
        $cutOrderIds = $orderIds->random(bcfloor($orderIds->count() * 0.7));

        $userIds = User::query()->pluck('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.7));

        foreach ($cutOrderIds as $orderId) {
            $numGroups = rand(1, floor($cutUserIds->count() * 0.3));

            $selectedUserIds = $cutUserIds->random($numGroups);

            foreach ($selectedUserIds as $userId) {
                OrderUser::create([
                    'order_id' => $orderId,
                    'user_id' => $userId,
                ]);
            }
        }
    }
}
