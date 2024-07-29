<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Currency;
use FluxErp\Rulesets\Currency\DeleteCurrencyRuleset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeleteCurrency extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCurrencyRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function performAction(): ?bool
    {
        $currency = resolve_static(Currency::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $currency->iso = $currency->iso . '___' . Hash::make(Str::uuid());
        $currency->save();

        return $currency->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Currency::class, 'query')
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
