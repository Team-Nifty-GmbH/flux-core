<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreatePaymentTypeRequest;
use FluxErp\Models\PaymentType;
use Illuminate\Support\Facades\Validator;

class CreatePaymentType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreatePaymentTypeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'payment-type.create';
    }

    public static function description(): string|null
    {
        return 'create payment type';
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function execute(): PaymentType
    {
        $paymentType = new PaymentType($this->data);
        $paymentType->save();

        return $paymentType;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
