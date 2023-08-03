<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentType;

class DeletePaymentType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:payment_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function performAction(): ?bool
    {
        return PaymentType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
