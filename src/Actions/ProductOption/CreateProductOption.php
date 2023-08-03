<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateProductOptionRequest;
use FluxErp\Models\ProductOption;
use Illuminate\Support\Facades\Validator;

class CreateProductOption extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateProductOptionRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function performAction(): ProductOption
    {
        $productOption = new ProductOption($this->data);
        $productOption->save();

        return $productOption->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOption());

        $this->data = $validator->validate();
    }
}
