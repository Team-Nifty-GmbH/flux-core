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
        // pluck() gibt eine Collection reiner IDs zurück
        $userIds = User::query()->pluck('id')->random(rand(5, 9));
        $commissionRateIds = CommissionRate::query()->pluck('id');
        $orderIds = Order::query()->pluck('id');
        $orderPositionIds = OrderPosition::query()->pluck('id');

        $userIds->each(function (int $userId) use ($commissionRateIds, $orderIds, $orderPositionIds): void {
            // direktes Find – kein data_get nötig
            $user = User::findOrFail($userId);

            $commissions = [];
            $count = rand(1, 3);

            for ($i = 0; $i < $count; $i++) {
                $params = ['user_id' => $user->id];

                if (rand(0, 1) === 1) {
                    $params['commission_rate_id'] = $commissionRateIds->random();
                }
                if (rand(0, 1) === 1) {
                    $params['order_id'] = $orderIds->random();
                }
                if (rand(0, 1) === 1) {
                    $params['order_position_id'] = $orderPositionIds->random();
                }
                if (rand(0, 1) === 1) {
                    $params['credit_note_order_position_id'] = $orderPositionIds->random();
                }

                $commissions[] = Commission::factory()->make($params)->toArray();
            }

            $user->commissions()->createMany($commissions);
        });
    }
}
