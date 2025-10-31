<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\MailAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailAccountFactory extends Factory
{
    protected $model = MailAccount::class;

    public function definition(): array
    {
        $email = fake()->safeEmail();

        return [
            'uuid' => fake()->uuid(),
            'name' => $email,
            'email' => $email,
            'password' => fake()->password(),
            'host' => fake()->domainName(),
            'smtp_email' => $email,
            'smtp_password' => fake()->password(),
            'smtp_host' => fake()->domainName(),
        ];
    }
}
