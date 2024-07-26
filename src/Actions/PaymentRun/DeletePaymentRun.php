<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentRun;
use FluxErp\Rulesets\PaymentRun\DeletePaymentRunRuleset;

class DeletePaymentRun extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeletePaymentRunRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PaymentRun::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(PaymentRun::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
