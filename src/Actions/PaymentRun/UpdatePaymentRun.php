<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\UpdatePaymentRunRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdatePaymentRun extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdatePaymentRunRuleset::class;
    }

    public static function models(): array
    {
        return [PaymentRun::class];
    }

    public function performAction(): Model
    {
        $payment = PaymentRun::query()
            ->whereKey($this->data['id'])
            ->first();

        $payment->fill($this->data);
        $payment->save();

        return $payment->withoutRelations()->fresh();
    }
}
