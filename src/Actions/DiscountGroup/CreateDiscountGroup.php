<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateDiscountGroupRequest;
use FluxErp\Models\DiscountGroup;
use Illuminate\Support\Arr;

class CreateDiscountGroup extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateDiscountGroupRequest())->rules();
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function execute(): DiscountGroup
    {
        $discounts = Arr::pull($this->data, 'discounts', []);

        $discountGroup = new DiscountGroup($this->data);
        $discountGroup->save();

        if ($discounts) {
            $discountGroup->discounts()->attach($discounts);
        }

        return $discountGroup;
    }
}
