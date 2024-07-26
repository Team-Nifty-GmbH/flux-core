<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentType;
use FluxErp\Rulesets\PaymentType\DeletePaymentTypeRuleset;

class DeletePaymentType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeletePaymentTypeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(PaymentType::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
