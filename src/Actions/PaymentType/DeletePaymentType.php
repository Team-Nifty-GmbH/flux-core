<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentType;
use FluxErp\Rulesets\PaymentType\DeletePaymentTypeRuleset;

class DeletePaymentType extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeletePaymentTypeRuleset::class;
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
