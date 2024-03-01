<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Currency;
use FluxErp\Rulesets\Currency\UpdateCurrencyRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCurrency extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateCurrencyRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function performAction(): Model
    {
        if ($this->data['is_default'] ?? false) {
            app(Currency::class)->query()
                ->whereKeyNot($this->data['id'])
                ->update(['is_default' => false]);
        }

        $currency = app(Currency::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $currency->fill($this->data);
        $currency->save();

        return $currency->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['iso'] .= ',' . ($this->data['id'] ?? 0);

        if (($this->data['is_default'] ?? false)
            && ! app(Currency::class)->query()
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }
}
