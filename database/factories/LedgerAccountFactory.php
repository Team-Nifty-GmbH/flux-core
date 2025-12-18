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
            'number' => fake()->randomNumber(),
            'name' => fake()->name(),
            'description' => fake()->realText(),
            'ledger_account_type_enum' => fake()->randomElement(LedgerAccountTypeEnum::values()),
            'is_automatic' => fake()->boolean(),
        ];
    }
}
