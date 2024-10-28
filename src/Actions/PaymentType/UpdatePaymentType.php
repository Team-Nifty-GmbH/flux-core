<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentType;
use FluxErp\Rulesets\PaymentType\UpdatePaymentTypeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdatePaymentType extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdatePaymentTypeRuleset::class;
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function performAction(): Model
    {
        $clients = Arr::pull($this->data, 'clients');

        $paymentType = resolve_static(PaymentType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $paymentType->fill($this->data);
        $paymentType->save();

        if (! is_null($clients)) {
            $paymentType->clients()->sync($clients);
        }

        return $paymentType->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        if (($this->data['is_default'] ?? false)
            && ! resolve_static(PaymentType::class, 'query')
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }

        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(PaymentType::class));

        $this->data = $validator->validate();
    }
}
