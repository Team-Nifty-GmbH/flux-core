<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateCurrencyRequest;
use FluxErp\Models\Currency;
use Illuminate\Database\Eloquent\Model;

class UpdateCurrency extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateCurrencyRequest())->rules();

        $this->rules['iso'] .= ',' . $this->data['id'];
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function performAction(): Model
    {
        if ($this->data['is_default'] ?? false) {
            Currency::query()
                ->whereKeyNot($this->data['id'])
                ->update(['is_default' => false]);
        }

        $currency = Currency::query()
            ->whereKey($this->data['id'])
            ->first();

        $currency->fill($this->data);
        $currency->save();

        return $currency->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        if (($this->data['is_default'] ?? false)
            && ! Currency::query()
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }

        parent::validateData();
    }
}
