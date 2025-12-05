<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\PaymentType\CreatePaymentTypeRuleset;
use Illuminate\Support\Arr;

class CreatePaymentType extends FluxAction
{
    public static function models(): array
    {
        return [PaymentType::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePaymentTypeRuleset::class;
    }

    public function performAction(): PaymentType
    {
        $tenants = Arr::pull($this->data, 'tenants');

        $paymentType = app(PaymentType::class, ['attributes' => $this->data]);
        $paymentType->save();

        if ($tenants) {
            $paymentType->tenants()->attach($tenants);
        }

        return $paymentType->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['tenants'] ??= [
            data_get($this->data, 'tenant_id') ?? resolve_static(Tenant::class, 'default')?->id,
        ];
    }
}
