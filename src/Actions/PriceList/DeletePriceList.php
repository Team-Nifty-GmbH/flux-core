<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\PriceList;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeletePriceList implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:price_lists,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'price-list.delete';
    }

    public static function description(): string|null
    {
        return 'delete price list';
    }

    public static function models(): array
    {
        return [PriceList::class];
    }

    public function execute(): bool|null
    {
        return PriceList::query()
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

        if (PriceList::query()
            ->whereKey($this->data['id'])
            ->first()
            ->prices()
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'prices' => [__('Price list has associated prices')],
            ])->errorBag('deletePriceList');
        }

        return $this;
    }
}
