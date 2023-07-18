<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateProductOptionRequest;
use FluxErp\Models\ProductOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateProductOption extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateProductOptionRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function execute(): Model
    {
        $productOption = ProductOption::query()
            ->whereKey($this->data['id'])
            ->first();

        $productOption->fill($this->data);
        $productOption->save();

        return $productOption->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOption());

        $this->data = $validator->validate();

        return $this;
    }
}
