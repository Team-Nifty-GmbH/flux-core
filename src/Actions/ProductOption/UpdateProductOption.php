<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateProductOptionRequest;
use FluxErp\Models\ProductOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateProductOption extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateProductOptionRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function performAction(): Model
    {
        $productOption = ProductOption::query()
            ->whereKey($this->data['id'])
            ->first();

        $productOption->fill($this->data);
        $productOption->save();

        return $productOption->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOption());

        $this->data = $validator->validate();
    }
}
