<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Currency;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeleteCurrency extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:currencies,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function performAction(): ?bool
    {
        $currency = Currency::query()
            ->whereKey($this->data['id'])
            ->first();

        $currency->iso = $currency->iso . '___' . Hash::make(Str::uuid());
        $currency->save();

        return $currency->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

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
    }
}
