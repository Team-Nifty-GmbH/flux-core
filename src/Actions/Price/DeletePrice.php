<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Price;
use Illuminate\Validation\ValidationException;

class DeletePrice extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:prices,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function execute(): bool|null
    {
        return Price::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

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

        return $this;
    }
}
