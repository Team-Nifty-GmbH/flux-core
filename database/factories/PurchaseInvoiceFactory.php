<?php

namespace FluxErp\Database\Factories;

use Carbon\Carbon;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseInvoiceFactory extends Factory
{
    protected $model = PurchaseInvoice::class;

    public function definition(): array
    {
        $from = Carbon::parse('2000-01-01 00:00:00');
        $to = Carbon::now();

        $systemDeliveryDate = Carbon::createFromTimestamp(rand($from->timestamp, $to->timestamp));
        $systemDeliveryDateEnd = $this->faker->boolean(70)
            ? Carbon::createFromTimestamp(rand($systemDeliveryDate->timestamp, $to->timestamp))->format('Y-m-d')
            : null;
        $systemDeliveryDate = $systemDeliveryDate->format('Y-m-d');

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
