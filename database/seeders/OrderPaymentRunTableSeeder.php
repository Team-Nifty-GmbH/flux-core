<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\Pivots\AddressAddressTypeOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class OrderPaymentRunTableSeeder extends Seeder
{
    public function run(): void
    {
        $orderIds = Order::query()->get('id');
        $cutOrderIds = $orderIds->random(bcfloor($orderIds->count() * 0.6));
        $paymentRunIds = PaymentRun::query()->get('id');
        $cutPaymentRunIds = $paymentRunIds->random(bcfloor($paymentRunIds->count() * 0.6));

        foreach ($cutOrderIds as $cutOrderId) {
            $cutOrderId->paymentRuns()->attach(
                $cutPaymentRunIds->random(rand(1, $cutPaymentRunIds->count())),
                [
                    'amount' => faker()->randomFloat(2, 1, 100),
                    'success' => faker()->boolean(),
                ]
            );
        }
    }
}
