<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PriceList;
use Illuminate\Validation\ValidationException;

class DeletePriceList extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:price_lists,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [PriceList::class];
    }

    public function performAction(): ?bool
    {
        return PriceList::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

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
    }
}
