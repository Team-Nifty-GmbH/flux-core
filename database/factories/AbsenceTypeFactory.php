<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\AbsenceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbsenceTypeFactory extends Factory
{
    protected $model = AbsenceType::class;

    public function definition(): array
    {
        $types = [
            ['name' => 'Urlaub', 'color' => '#10B981', 'is_vacation' => true],
            ['name' => 'Krankheit', 'color' => '#EF4444', 'is_vacation' => false],
            ['name' => 'Homeoffice', 'color' => '#3B82F6', 'is_vacation' => false],
            ['name' => 'Fortbildung', 'color' => '#8B5CF6', 'is_vacation' => false],
            ['name' => 'Dienstreise', 'color' => '#F59E0B', 'is_vacation' => false],
            ['name' => 'Sonderurlaub', 'color' => '#EC4899', 'is_vacation' => false],
        ];

        $type = $this->faker->randomElement($types);

        return [
            'name' => $type['name'],
            'color' => $type['color'],
            'is_active' => true,
            'can_select_substitute' => $this->faker->boolean(30),
            'must_select_substitute' => false,
            'requires_proof' => $type['name'] === 'Krankheit',
            'requires_reason' => $this->faker->boolean(20),
            'employee_can_create' => $this->faker->randomElement(['yes', 'approval_required']),
            'counts_as_work_day' => !in_array($type['name'], ['Urlaub', 'Krankheit']),
            'counts_as_target_hours' => !in_array($type['name'], ['Urlaub', 'Krankheit']),
            'requires_work_day' => false,
            'is_vacation' => $type['is_vacation'],
            'client_id' => 1,
        ];
    }
}