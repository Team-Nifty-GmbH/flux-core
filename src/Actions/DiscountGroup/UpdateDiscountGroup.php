<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateDiscountGroupRequest;
use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateDiscountGroup extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateDiscountGroupRequest())->rules();
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function performAction(): Model
    {
        $discounts = Arr::pull($this->data, 'discounts');

        $discountGroup = DiscountGroup::query()
            ->whereKey($this->data['id'])
            ->first();

        $discountGroup->fill($this->data);
        $discountGroup->save();

        if (! is_null($discounts)) {
            $discountGroup->discounts()->sync($discounts);
        }

        return $discountGroup->withoutRelations()->fresh();
    }
}
