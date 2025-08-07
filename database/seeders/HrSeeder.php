<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\AbsenceType;
use FluxErp\Models\Holiday;
use FluxErp\Models\Location;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Models\WorkTimeModelSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedWorkTimeModels();
        $this->seedAbsenceTypes();
        $this->seedLocations();
        $this->seedHolidays();
    }

    protected function seedWorkTimeModels(): void
    {
        $models = [
            [
                'name' => 'Vollzeit',
                'cycle_weeks' => 1,
                'weekly_hours' => 40,
                'annual_vacation_days' => 30,
                'max_overtime_hours' => 200,
                'overtime_compensation' => 'time_off',
                'is_active' => true,
                'client_id' => 1,
                'schedules' => [
                    ['week_number' => 1, 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '17:00', 'break_minutes' => 60],
                    ['week_number' => 1, 'day_of_week' => 2, 'start_time' => '08:00', 'end_time' => '17:00', 'break_minutes' => 60],
                    ['week_number' => 1, 'day_of_week' => 3, 'start_time' => '08:00', 'end_time' => '17:00', 'break_minutes' => 60],
                    ['week_number' => 1, 'day_of_week' => 4, 'start_time' => '08:00', 'end_time' => '17:00', 'break_minutes' => 60],
                    ['week_number' => 1, 'day_of_week' => 5, 'start_time' => '08:00', 'end_time' => '16:00', 'break_minutes' => 60],
                ]
            ],
            [
                'name' => 'Teilzeit 80%',
                'cycle_weeks' => 1,
                'weekly_hours' => 32,
                'annual_vacation_days' => 24,
                'max_overtime_hours' => 150,
                'overtime_compensation' => 'time_off',
                'is_active' => true,
                'client_id' => 1,
                'schedules' => [
                    ['week_number' => 1, 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '17:00', 'break_minutes' => 60],
                    ['week_number' => 1, 'day_of_week' => 2, 'start_time' => '08:00', 'end_time' => '17:00', 'break_minutes' => 60],
                    ['week_number' => 1, 'day_of_week' => 3, 'start_time' => '08:00', 'end_time' => '17:00', 'break_minutes' => 60],
                    ['week_number' => 1, 'day_of_week' => 4, 'start_time' => '08:00', 'end_time' => '13:00', 'break_minutes' => 0],
                ]
            ],
        ];

        foreach ($models as $modelData) {
            $schedules = $modelData['schedules'];
            unset($modelData['schedules']);
            
            $model = WorkTimeModel::create($modelData);
            
            foreach ($schedules as $schedule) {
                $schedule['work_time_model_id'] = $model->id;
                WorkTimeModelSchedule::create($schedule);
            }
        }
    }

    protected function seedAbsenceTypes(): void
    {
        $types = [
            [
                'name' => 'Urlaub',
                'color' => '#10B981',
                'is_active' => true,
                'can_select_substitute' => true,
                'must_select_substitute' => false,
                'requires_proof' => false,
                'requires_reason' => false,
                'employee_can_create' => 'approval_required',
                'counts_as_work_day' => false,
                'counts_as_target_hours' => false,
                'requires_work_day' => false,
                'is_vacation' => true,
                'client_id' => 1,
            ],
            [
                'name' => 'Krankheit',
                'color' => '#EF4444',
                'is_active' => true,
                'can_select_substitute' => false,
                'must_select_substitute' => false,
                'requires_proof' => true,
                'requires_reason' => false,
                'employee_can_create' => 'yes',
                'counts_as_work_day' => false,
                'counts_as_target_hours' => false,
                'requires_work_day' => false,
                'is_vacation' => false,
                'client_id' => 1,
            ],
            [
                'name' => 'Homeoffice',
                'color' => '#3B82F6',
                'is_active' => true,
                'can_select_substitute' => false,
                'must_select_substitute' => false,
                'requires_proof' => false,
                'requires_reason' => false,
                'employee_can_create' => 'yes',
                'counts_as_work_day' => true,
                'counts_as_target_hours' => true,
                'requires_work_day' => true,
                'is_vacation' => false,
                'client_id' => 1,
            ],
            [
                'name' => 'Fortbildung',
                'color' => '#8B5CF6',
                'is_active' => true,
                'can_select_substitute' => false,
                'must_select_substitute' => false,
                'requires_proof' => false,
                'requires_reason' => true,
                'employee_can_create' => 'approval_required',
                'counts_as_work_day' => true,
                'counts_as_target_hours' => true,
                'requires_work_day' => false,
                'is_vacation' => false,
                'client_id' => 1,
            ],
            [
                'name' => 'Dienstreise',
                'color' => '#F59E0B',
                'is_active' => true,
                'can_select_substitute' => false,
                'must_select_substitute' => false,
                'requires_proof' => false,
                'requires_reason' => true,
                'employee_can_create' => 'approval_required',
                'counts_as_work_day' => true,
                'counts_as_target_hours' => true,
                'requires_work_day' => false,
                'is_vacation' => false,
                'client_id' => 1,
            ],
        ];

        foreach ($types as $type) {
            AbsenceType::create($type);
        }
    }

    protected function seedLocations(): void
    {
        $locations = [
            [
                'name' => 'Hauptsitz Berlin',
                'address' => 'Alexanderplatz 1',
                'postal_code' => '10178',
                'city' => 'Berlin',
                'country' => 'DE',
                'phone' => '+49 30 123456',
                'email' => 'berlin@example.com',
                'is_active' => true,
                'client_id' => 1,
            ],
            [
                'name' => 'Niederlassung München',
                'address' => 'Marienplatz 1',
                'postal_code' => '80331',
                'city' => 'München',
                'country' => 'DE',
                'phone' => '+49 89 123456',
                'email' => 'muenchen@example.com',
                'is_active' => true,
                'client_id' => 1,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }

    protected function seedHolidays(): void
    {
        $year = Carbon::now()->year;
        $holidays = [
            ['name' => 'Neujahr', 'date' => "$year-01-01", 'location_id' => null, 'client_id' => 1],
            ['name' => 'Heilige Drei Könige', 'date' => "$year-01-06", 'location_id' => 2, 'client_id' => 1],
            ['name' => 'Tag der Arbeit', 'date' => "$year-05-01", 'location_id' => null, 'client_id' => 1],
            ['name' => 'Tag der Deutschen Einheit', 'date' => "$year-10-03", 'location_id' => null, 'client_id' => 1],
            ['name' => 'Weihnachten', 'date' => "$year-12-25", 'location_id' => null, 'client_id' => 1],
            ['name' => '2. Weihnachtsfeiertag', 'date' => "$year-12-26", 'location_id' => null, 'client_id' => 1],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }
    }
}