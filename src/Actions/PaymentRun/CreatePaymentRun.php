<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\CreatePaymentRunRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreatePaymentRun extends FluxAction
{
    public static function models(): array
    {
        return [PaymentRun::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePaymentRunRuleset::class;
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
