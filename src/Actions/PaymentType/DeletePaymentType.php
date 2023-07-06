<?php

namespace FluxErp\Actions\PaymentType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\PaymentType;
use Illuminate\Support\Facades\Validator;

class DeletePaymentType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:payment_types,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'payment-type.delete';
    }

    public static function description(): string|null
    {
        return 'delete payment type';
    }

    public static function models(): array
    {
        return [PaymentType::class];
    }

    public function execute()
    {
        return PaymentType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
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
