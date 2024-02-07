<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentReminder;

class DeletePaymentReminder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:payment_reminders,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [PaymentReminder::class];
    }

    public function performAction(): ?bool
    {
        return PaymentReminder::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
