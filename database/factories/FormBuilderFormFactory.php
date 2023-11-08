<?php

namespace FluxErp\Database\Factories;

use App\Models\User;
use FluxErp\Models\FormBuilderForm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FormBuilderFormFactory extends Factory
{
    protected $model = FormBuilderForm::class;

    public function definition(): array
    {
        $user = User::all();

        return [
            'user_id' => $user->random(),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'slug' => $this->faker->slug(),
            'is_active' => $this->faker->boolean(),
            'start_date' => Carbon::now()->subWeeks(rand(1, 52)),
            'end_date' => Carbon::now()->addWeeks(rand(1, 52)),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
