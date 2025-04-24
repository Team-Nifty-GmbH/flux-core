<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Commission;
use FluxErp\Models\CommissionRate;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class CommissionTableSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::query()->get('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.7));

        $commissionRateIds = CommissionRate::query()->get('id');
        $cutCommissionRateIds = $commissionRateIds->random(bcfloor($commissionRateIds->count() * 0.7));

        $orderIds = Order::query()->get('id');
        $cutOrderIds = $orderIds->random(bcfloor($orderIds->count() * 0.7));

        $orderPositionIds = OrderPosition::query()->get('id');
        $cutOrderPositionIds = $orderPositionIds->random(bcfloor($orderPositionIds->count() * 0.7));

        foreach ($cutUserIds as $userId) {
            Commission::factory()->count(rand(1, 3))->create([
                'user_id' => $userId,
                'commission_rate_id' => fn () => faker()->boolean() ? $cutCommissionRateIds->random()->getKey() : null,
                'order_id' => fn () => faker()->boolean() ? $cutOrderIds->random()->getKey() : null,
                'order_position_id' => fn () => faker()->boolean() ? $cutOrderPositionIds->random()->getKey() : null,
                'credit_note_order_position_id' => fn () => faker()->boolean() ? $cutOrderPositionIds->random()->getKey() : null,
            ]);
        }
    }
}
