<?php

namespace FluxErp\Database\Seeders;

use App\Models\User;
use FluxErp\Models\Contact;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use Illuminate\Database\Seeder;

class WorkTimeTableSeeder extends Seeder
{
    public function run()
    {
        $users = User::all(['id']);
        $workTimeTypes = WorkTimeType::all(['id']);
        $contacts = Contact::all(['id']);

        foreach ($users as $user) {
            $dailyWorkTimes = WorkTime::factory(30)->create([
                'user_id' => $user->id,
                'work_time_type_id' => $workTimeTypes->random()->id,
                'is_daily_work_time' => true,
            ]);

            foreach ($dailyWorkTimes as $dailyWorkTime) {
                $startedAt = fake()->dateTimeBetween($dailyWorkTime->started_at, $dailyWorkTime->ended_at);
                WorkTime::factory(80)->create([
                    'user_id' => $user->id,
                    'contact_id' => fn() => fake()->boolean(70)
                           ? $contacts->random()->id
                           : null,
                    'started_at' => $startedAt,
                    'ended_at' => fn() => fake()->dateTimeBetween($startedAt, $dailyWorkTime->ended_at),
                    'parent_id' => $dailyWorkTime->id,
                    'work_time_type_id' => fn() => $workTimeTypes->random()->id,
                    'is_daily_work_time' => false,
                ]);
            }
        }
    }
}
