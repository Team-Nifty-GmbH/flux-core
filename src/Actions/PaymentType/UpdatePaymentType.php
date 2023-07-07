<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdatePaymentTypeRequest;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdatePaymentType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdatePaymentTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function execute(): Model
    {
        $paymentType = PaymentType::query()
            ->whereKey($this->data['id'])
            ->first();

        $paymentType->fill($this->data);
        $paymentType->save();

        return $paymentType->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new PaymentType());

        $this->data = $validator->validate();

        return $this;
    }
}
