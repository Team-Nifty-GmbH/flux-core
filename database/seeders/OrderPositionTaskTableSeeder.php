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
        $orderPositionIds = OrderPosition::query()->pluck('id');
        $cutOrderPositionIds = $orderPositionIds->random(bcfloor($orderPositionIds->count() * 0.3));

        $taskIds = Task::query()->pluck('id');
        $cutTaskIds = $taskIds->random(bcfloor($taskIds->count() * 0.6));

        foreach ($cutOrderPositionIds as $orderPositionId) {
            $numGroups = rand(1, floor($cutTaskIds->count() * 0.1));

            $selectedTaskIds = $cutTaskIds->random($numGroups);

            foreach ($selectedTaskIds as $taskId) {
                OrderPositionTask::create([
                    'order_position_id' => $orderPositionId,
                    'task_id' => $taskId,
                    'amount' => rand(1, 100),
                ]);
            }
        }
    }
}
