<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\LedgerAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class LedgerAccountFactory extends Factory
{
    protected $model = LedgerAccount::class;

    public function definition(): array
    {
        // number, ledger_account_type_enum and tenant_id are unique together, so a
        // number drawn without looking at the table collides sooner or later. Start
        // above every row there is and let unique() keep one batch apart, because
        // definition() runs for the whole batch before the first row is written.
        //
        // number is a varchar, so MAX() would sort it lexicographically and rank '9'
        // above '1000000000'. The global scopes come off on purpose as well: the
        // tenant scope hides rows of other tenants, whose numbers are taken too.
        $highest = (int) resolve_static(LedgerAccount::class, 'query')
            ->withoutGlobalScopes()
            ->max(DB::raw('number + 0'));

        return [
            'number' => fake()->unique()->numberBetween($highest + 1, $highest + 1000000),
            'name' => fake()->name(),
            'description' => fake()->realText(),
            'ledger_account_type_enum' => fake()->randomElement(LedgerAccountTypeEnum::values()),
            'is_automatic' => fake()->boolean(),
        ];
    }
}
