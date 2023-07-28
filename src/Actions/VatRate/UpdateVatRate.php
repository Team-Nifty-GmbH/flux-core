<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateVatRateRequest;
use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\Model;

class UpdateVatRate extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateVatRateRequest())->rules();
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function performAction(): Model
    {
        $vatRate = VatRate::query()
            ->whereKey($this->data['id'])
            ->first();

        $vatRate->fill($this->data);
        $vatRate->save();

        return $vatRate->withoutRelations()->fresh();
    }
}
