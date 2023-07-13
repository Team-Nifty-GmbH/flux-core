<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateDiscountGroupRequest;
use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateDiscountGroup extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateDiscountGroupRequest())->rules();
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function execute(): Model
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
