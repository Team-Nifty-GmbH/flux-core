<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreatePaymentTypeRequest;
use FluxErp\Models\PaymentType;
use Illuminate\Support\Facades\Validator;

class CreatePaymentType extends FluxAction
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
        $this->data['is_default'] = ! PaymentType::query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            PaymentType::query()->update(['is_default' => false]);
        }

        $paymentType = new PaymentType($this->data);
        $paymentType->save();

        return $paymentType->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new PaymentType());

        $this->data = $validator->validate();
    }
}
