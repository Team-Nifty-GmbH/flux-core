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

        $this->rules['iso'] = $this->rules['iso'] . ',' . $this->data['id'];
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function performAction(): Model
    {
        $this->data['is_default'] = ! Currency::query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            Currency::query()->update(['is_default' => false]);
        }

        $currency = Currency::query()
            ->whereKey($this->data['id'])
            ->first();

        $currency->fill($this->data);
        $currency->save();

        return $currency->withoutRelations()->fresh();
    }
}
