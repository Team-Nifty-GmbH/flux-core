<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use FluxErp\Rulesets\PaymentType\CreatePaymentTypeRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreatePaymentType extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreatePaymentTypeRuleset::class;
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function performAction(): PaymentType
    {
        $clients = Arr::pull($this->data, 'clients');

        $paymentType = app(PaymentType::class, ['attributes' => $this->data]);
        $paymentType->save();

        if ($clients) {
            $paymentType->clients()->attach($clients);
        }

        return $paymentType->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(PaymentType::class));

        $this->data = $validator->validate();
    }

    protected function prepareForValidation(): void
    {
        $this->data['clients'] ??= [
            data_get($this->data, 'client_id') ?? resolve_static(Client::class, 'default')?->id,
        ];
    }
}
