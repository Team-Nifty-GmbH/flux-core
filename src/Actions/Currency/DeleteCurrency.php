<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Currency;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeleteCurrency extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:currencies,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function execute(): ?bool
    {
        $currency = Currency::query()
            ->whereKey($this->data['id'])
            ->first();

        $currency->iso = $currency->iso . '___' . Hash::make(Str::uuid());
        $currency->save();

        return $currency->delete();
    }

    public function validate(): static
    {
        parent::validate();

        if (Currency::query()
            ->whereKey($this->data['id'])
            ->first()
            ->countries()
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'country' => [__('Currency referenced by a country')],
            ])->errorBag('deleteCurrency');
        }

        return $this;
    }
}
