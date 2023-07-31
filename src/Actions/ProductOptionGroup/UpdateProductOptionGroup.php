<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateProductOptionGroupRequest;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateProductOptionGroup extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateProductOptionGroupRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    public function performAction(): Model
    {
        $productOptionGroup = ProductOptionGroup::query()
            ->whereKey($this->data['id'])
            ->first();

        $productOptionGroup->fill($this->data);
        $productOptionGroup->save();

        return $productOptionGroup->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOptionGroup());

        $this->data = $validator->validate();
    }
}
