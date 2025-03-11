<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductOption;
use FluxErp\Rulesets\ProductOption\UpdateProductOptionRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateProductOption extends FluxAction
{
    public static function models(): array
    {
        return [ProductOption::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProductOptionRuleset::class;
    }

    public function performAction(): Model
    {
        $productOption = resolve_static(ProductOption::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $productOption->fill($this->data);
        $productOption->save();

        return $productOption->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(ProductOption::class));

        $this->data = $validator->validate();
    }
}
