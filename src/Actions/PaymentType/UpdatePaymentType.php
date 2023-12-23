<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdatePaymentTypeRequest;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdatePaymentType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdatePaymentTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function performAction(): Model
    {
        if ($this->data['is_default'] ?? false) {
            PaymentType::query()->update(['is_default' => false]);
        }

        $paymentType = PaymentType::query()
            ->whereKey($this->data['id'])
            ->first();

        $paymentType->fill($this->data);
        $paymentType->save();

        return $paymentType->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        if (($this->data['is_default'] ?? false)
            && ! PaymentType::query()
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }

        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new PaymentType());

        $this->data = $validator->validate();
    }
}
