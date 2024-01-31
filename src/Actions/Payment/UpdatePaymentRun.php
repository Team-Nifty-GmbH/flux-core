<?php

namespace FluxErp\Actions\Payment;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdatePaymentRunRequest;
use FluxErp\Models\PaymentRun;

class UpdatePaymentRun extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdatePaymentRunRequest())->rules();
    }

    public static function models(): array
    {
        return [PaymentRun::class];
    }

    public function performAction(): mixed
    {
        $payment = PaymentRun::query()
            ->whereKey($this->data['id'])
            ->first();

        $payment->fill($this->data);
        $payment->save();

        return $payment->withoutRelations()->fresh();
    }
}
