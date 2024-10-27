<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\CreatePaymentRunRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreatePaymentRun extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return CreatePaymentRunRuleset::class;
    }

    public static function models(): array
    {
        return [PaymentRun::class];
    }

    public function performAction(): Model
    {
        $orders = Arr::pull($this->data, 'orders');

        $payment = app(PaymentRun::class, ['attributes' => $this->data]);
        $payment->save();

        $payment->orders()->attach($orders);

        return $payment->fresh();
    }
}
