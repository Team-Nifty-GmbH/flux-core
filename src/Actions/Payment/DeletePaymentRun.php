<?php

namespace FluxErp\Actions\Payment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentRun;

class DeletePaymentRun extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:payment_runs,id',
        ];
    }

    public static function models(): array
    {
        return [PaymentRun::class];
    }

    public function performAction(): mixed
    {
        return PaymentRun::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
