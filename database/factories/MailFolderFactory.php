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
            'uuid' => fake()->uuid(),
            'name' => fake()->name,
            'slug' => fake()->slug,
            'can_create_ticket' => fake()->boolean,
            'can_create_purchase_invoice' => fake()->boolean,
            'can_create_lead' => fake()->boolean,
        ];
    }
}
