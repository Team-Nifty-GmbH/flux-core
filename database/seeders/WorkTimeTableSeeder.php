<?php

namespace FluxErp\Database\Seeders;

use App\Models\User;
use FluxErp\Models\Contact;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use Illuminate\Database\Seeder;

class WorkTimeTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);
        $workTimeTypes = WorkTimeType::all(['id']);
        $contacts = Contact::all(['id']);

        foreach ($users as $user) {
            $dailyWorkTimes = WorkTime::factory(15)->create([
                'user_id' => $user->id,
                'is_daily_work_time' => true,
                'is_pause' => false,
            ]);

            foreach ($dailyWorkTimes as $dailyWorkTime) {
                WorkTime::factory(30)->create(function () use ($user, $dailyWorkTime, $workTimeTypes, $contacts) {
                    $startedAt = fake()->dateTimeBetween($dailyWorkTime->started_at, $dailyWorkTime->ended_at);

                    return [
                        'user_id' => $user->id,
                        'contact_id' => fn () => fake()->boolean(70)
                            ? $contacts->random()->id
                            : null,
                        'started_at' => $startedAt,
                        'ended_at' => fake()->dateTimeBetween($startedAt, $dailyWorkTime->ended_at),
                        'parent_id' => $dailyWorkTime->id,
                        'work_time_type_id' => $workTimeTypes->random()->id,
                        'is_daily_work_time' => false,
                    ];
                });
            }
        }
    }
}
