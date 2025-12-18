<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PurchaseInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseInvoiceFactory extends Factory
{
    protected $model = PurchaseInvoice::class;

    public function definition(): array
    {
        return [
            'invoice_date' => fake()->date(),
            'invoice_number' => Str::uuid()->toString(),
            'hash' => md5(Str::uuid()->toString()),
            'is_net' => fake()->boolean(80),
        ];
    }
}
