<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreatePaymentTypeRequest;
use FluxErp\Models\PaymentType;
use Illuminate\Support\Facades\Validator;

class CreatePaymentType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreatePaymentTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function execute(): PaymentType
    {
        $paymentType = new PaymentType($this->data);
        $paymentType->save();

        return $paymentType->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new PaymentType());

        $this->data = $validator->validate();

        return $this;
    }
}
