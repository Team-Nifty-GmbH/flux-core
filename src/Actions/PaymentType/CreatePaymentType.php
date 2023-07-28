<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreatePaymentTypeRequest;
use FluxErp\Models\PaymentType;
use Illuminate\Support\Facades\Validator;

class CreatePaymentType extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreatePaymentTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function performAction(): PaymentType
    {
        $paymentType = new PaymentType($this->data);
        $paymentType->save();

        return $paymentType;
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new PaymentType());

        $this->data = $validator->validate();
    }
}
