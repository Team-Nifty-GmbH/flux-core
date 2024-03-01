<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\UpdatePaymentRunRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdatePaymentRun extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdatePaymentRunRuleset::class, 'getRules');
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
