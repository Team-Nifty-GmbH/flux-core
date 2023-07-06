<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateDiscountRequest;
use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateDiscount implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateDiscountRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'discount.update';
    }

    public static function description(): string|null
    {
        return 'update discount';
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function execute(): Model
    {
        $discount = Discount::query()
            ->whereKey($this->data['id'])
            ->first();

        $discount->fill($this->data);
        $discount->save();

        return $discount->withoutRelations()->fresh();
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
