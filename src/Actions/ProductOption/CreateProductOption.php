<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateProductOptionRequest;
use FluxErp\Models\ProductOption;
use Illuminate\Support\Facades\Validator;

class CreateProductOption extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateProductOptionRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function execute(): ProductOption
    {
        $productOption = new ProductOption($this->data);
        $productOption->save();

        return $productOption;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOption());

        $this->data = $validator->validate();

        return $this;
    }
}
