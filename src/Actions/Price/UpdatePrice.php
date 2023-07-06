<?php

namespace FluxErp\Actions\Price;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdatePriceRequest;
use FluxErp\Models\Price;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdatePrice implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdatePriceRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'price.update';
    }

    public static function description(): string|null
    {
        return 'update price';
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function execute(): Model
    {
        $price = Price::query()
            ->whereKey($this->data['id'])
            ->first();

        $price->fill($this->data);
        $price->save();

        return $price->withoutRelations()->fresh();
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
