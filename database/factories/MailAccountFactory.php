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
            'email' => $this->faker->safeEmail(),
            'password' => $this->faker->password(),
            'host' => $this->faker->domainName(),
            'smtp_email' => $this->faker->safeEmail(),
            'smtp_password' => $this->faker->password(),
            'smtp_host' => $this->faker->domainName(),
        ];
    }
}
