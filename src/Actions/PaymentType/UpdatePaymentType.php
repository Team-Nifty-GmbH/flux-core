<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PaymentType;
use FluxErp\Rulesets\PaymentType\UpdatePaymentTypeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdatePaymentType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdatePaymentTypeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function performAction(): Model
    {
        $paymentType = app(PaymentType::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $paymentType->fill($this->data);
        $paymentType->save();

        return $paymentType->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        if (($this->data['is_default'] ?? false)
            && ! app(PaymentType::class)->query()
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
