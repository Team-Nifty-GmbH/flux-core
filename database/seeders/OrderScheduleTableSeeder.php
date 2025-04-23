<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Order;
use FluxErp\Models\Pivots\OrderSchedule;
use FluxErp\Models\Schedule;
use Illuminate\Database\Seeder;

class OrderScheduleTableSeeder extends Seeder
{
    public function run(): void
    {
        $orderIds = Order::query()->get('id');
        $cutOrderIds = $orderIds->random(bcfloor($orderIds->count() * 0.6));
        $scheduleIds = Schedule::query()->get('id');
        $cutScheduleIds = $scheduleIds->random(bcfloor($scheduleIds->count() * 0.7));

        foreach ($cutScheduleIds as $cutScheduleIdId) {
            $numGroups = rand(1, floor($cutOrderIds->count() * 0.5));

            $ids = $cutOrderIds->random($numGroups)->pluck('id')->toArray();

            foreach ($ids as $id) {
                OrderSchedule::factory()->create([
                    'schedule_id' => $cutScheduleIdId,
                    'order_id' => $id,
                ]);
            }
        }
    }
}
