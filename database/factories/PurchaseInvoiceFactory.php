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
        $systemDeliveryDate = $this->faker->date();
        $systemDeliveryDateEnd = $this->faker->boolean(70)
            ? $this->faker->dateTimeBetween($systemDeliveryDate)->format('Y-m-d')
            : null;

        return [
            'invoice_date' => $this->faker->date(),
            'system_delivery_date' => $systemDeliveryDate,
            'system_delivery_date_end' => $systemDeliveryDateEnd,
            'invoice_number' => Str::uuid()->toString(),
            'hash' => md5(Str::uuid()->toString()),
            'iban' => $this->faker->iban(),
            'account_holder' => $this->faker->company(),
            'bank_name' => $this->faker->company(),
            'bic' => $this->faker->bothify('##????##?#?'),
            'is_net' => $this->faker->boolean(80),
        ];
    }
}
