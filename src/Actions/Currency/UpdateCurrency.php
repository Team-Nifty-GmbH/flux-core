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
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function performAction(): Model
    {
        $currency = Currency::query()
            ->whereKey($this->data['id'])
            ->first();

        $currency->fill($this->data);
        $currency->save();

        return $currency->withoutRelations()->fresh();
    }
}
