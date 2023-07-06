<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateDiscountRequest;
use FluxErp\Models\Discount;
use Illuminate\Support\Facades\Validator;

class CreateDiscount implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateDiscountRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'discount.create';
    }

    public static function description(): string|null
    {
        return 'create discount';
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function execute(): Discount
    {
        $discount = new Discount($this->data);
        $discount->save();

        return $discount;
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
