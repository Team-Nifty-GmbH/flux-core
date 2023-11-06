<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateCommissionRequest;
use FluxErp\Models\Commission;
use Illuminate\Database\Eloquent\Model;

class UpdateCommission extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = (new UpdateCommissionRequest())->rules();
    }

    public static function models(): array
    {
        return [Commission::class];
    }

    public function performAction(): Model
    {
        $commission = Commission::query()
            ->whereKey($this->data['id'])
            ->first();

        $commission->fill($this->data)
            ->save();

        return $commission->withoutRelations()->fresh();
    }
}
