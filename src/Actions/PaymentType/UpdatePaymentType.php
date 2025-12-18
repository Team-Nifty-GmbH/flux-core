<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentType;
use FluxErp\Rulesets\PaymentType\UpdatePaymentTypeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdatePaymentType extends FluxAction
{
    public static function models(): array
    {
        return [PaymentType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePaymentTypeRuleset::class;
    }

    public function performAction(): Model
    {
        $tenants = Arr::pull($this->data, 'tenants');

        $paymentType = resolve_static(PaymentType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $paymentType->fill($this->data);
        $paymentType->save();

        if (! is_null($tenants)) {
            $paymentType->tenants()->sync($tenants);
        }

        return $paymentType->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (($this->data['is_default'] ?? false)
            && ! resolve_static(PaymentType::class, 'query')
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }
}
