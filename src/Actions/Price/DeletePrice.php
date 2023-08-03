<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Price;
use Illuminate\Validation\ValidationException;

class DeletePrice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:prices,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function performAction(): ?bool
    {
        return Price::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        if (Price::query()
            ->whereKey($this->data['id'])
            ->first()
            ->orderPositions()
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'order_positions' => [__('Price has associated order positions')],
            ])->errorBag('deletePrice');
        }
    }
}
