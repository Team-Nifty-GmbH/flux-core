<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
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
        $clients = Arr::pull($this->data, 'clients');

        $paymentType = app(PaymentType::class, ['attributes' => $this->data]);
        $paymentType->save();

        if ($clients) {
            $paymentType->clients()->attach($clients);
        }

        return $paymentType->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['clients'] ??= [
            data_get($this->data, 'client_id') ?? resolve_static(Client::class, 'default')?->id,
        ];
    }
}
