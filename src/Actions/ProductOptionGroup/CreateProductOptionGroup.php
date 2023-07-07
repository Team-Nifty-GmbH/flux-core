<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateProductOptionGroupRequest;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Support\Facades\Validator;

class CreateProductOptionGroup extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateProductOptionGroupRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    public function execute(): ProductOptionGroup
    {
        $productOptionGroup = new ProductOptionGroup($this->data);
        $productOptionGroup->save();

        return $productOptionGroup;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOptionGroup());

        $this->data = $validator->validate();

        return $this;
    }
}
