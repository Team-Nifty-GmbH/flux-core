<?php

namespace FluxErp\Actions\CommissionRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateCommissionRateRequest;
use FluxErp\Models\CommissionRate;
use Illuminate\Database\Eloquent\Model;

class UpdateCommissionRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = (new UpdateCommissionRateRequest())->rules();
    }

    public static function models(): array
    {
        return [CommissionRate::class];
    }

    public function performAction(): Model
    {
        $commissionRate = CommissionRate::query()
            ->whereKey($this->data['id'])
            ->first();

        $commissionRate->fill($this->data)
            ->save();

        return $commissionRate->withoutRelations()->fresh();
    }
}
