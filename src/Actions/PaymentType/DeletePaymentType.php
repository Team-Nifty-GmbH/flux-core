<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\PaymentType;

class DeletePaymentType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:payment_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function execute(): bool|null
    {
        return PaymentType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
