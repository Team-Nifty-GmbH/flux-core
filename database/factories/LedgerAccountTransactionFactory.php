<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Pivots\LedgerAccountTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class LedgerAccountTransactionFactory extends Factory
{
    protected $model = LedgerAccountTransaction::class;

    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
