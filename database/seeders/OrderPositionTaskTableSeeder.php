<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Pivots\OrderPositionTask;
use FluxErp\Models\Task;
use Illuminate\Database\Seeder;

class OrderPositionTaskTableSeeder extends Seeder
{
    public function run(): void
    {
        $orderPositionIds = OrderPosition::query()->get('id');
        $cutOrderPositionIds = $orderPositionIds->random(bcfloor($orderPositionIds->count() * 0.3));

        $taskIds = Task::query()->get('id');
        $cutTaskIds = $taskIds->random(bcfloor($taskIds->count() * 0.6));

        foreach ($cutOrderPositionIds as $cutOrderPositionId) {
            $numGroups = rand(1, floor($cutTaskIds->count() * 0.1));

            $ids = $cutTaskIds->random($numGroups)->pluck('id')->toArray();

            foreach ($ids as $id) {
                OrderPositionTask::factory()->create([
                    'order_position_id' => $cutOrderPositionId,
                    'task_id' => $id,
                ]);
            }
        }
    }
}
