<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\LedgerAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class LedgerAccountFactory extends Factory
{
    protected $model = LedgerAccount::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->randomNumber(),
            'name' => $this->faker->name(),
            'description' => $this->faker->realText(),
            'ledger_account_type_enum' => $this->faker->randomElement(LedgerAccountTypeEnum::values()),
            'is_automatic' => $this->faker->boolean(),
        ];
    }
}
