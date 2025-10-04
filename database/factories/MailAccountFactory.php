<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\MailAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailAccountFactory extends Factory
{
    protected $model = MailAccount::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'email' => fake()->safeEmail(),
            'password' => fake()->password(),
            'host' => fake()->domainName(),
            'smtp_email' => fake()->safeEmail(),
            'smtp_password' => fake()->password(),
            'smtp_host' => fake()->domainName(),
        ];
    }
}
