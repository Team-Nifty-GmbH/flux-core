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
        // number, ledger_account_type_enum and tenant_id are unique together, so a
        // number drawn without looking at the table collides sooner or later. Start
        // above every row there is, ignoring the tenant scope so the highest number
        // of another tenant counts too, and let unique() keep one batch apart.
        $highest = (int) resolve_static(LedgerAccount::class, 'query')
            ->withoutGlobalScopes()
            ->max('number');

        return [
            'number' => fake()->unique()->numberBetween($highest + 1, $highest + 1000000),
            'name' => fake()->name(),
            'description' => fake()->realText(),
            'ledger_account_type_enum' => fake()->randomElement(LedgerAccountTypeEnum::values()),
            'is_automatic' => fake()->boolean(),
        ];
    }
}
