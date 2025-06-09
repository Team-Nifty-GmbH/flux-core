<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\MailFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailFolderFactory extends Factory
{
    protected $model = MailFolder::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->name,
            'slug' => $this->faker->slug,
            'can_create_ticket' => $this->faker->boolean,
            'can_create_purchase_invoice' => $this->faker->boolean,
            'can_create_lead' => $this->faker->boolean,
        ];
    }
}
