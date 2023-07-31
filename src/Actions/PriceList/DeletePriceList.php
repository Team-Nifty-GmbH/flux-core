<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\PriceList;
use Illuminate\Validation\ValidationException;

class DeletePriceList extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:price_lists,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [PriceList::class];
    }

    public function execute(): ?bool
    {
        return PriceList::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

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
