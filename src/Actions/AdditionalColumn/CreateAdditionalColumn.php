<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rulesets\AdditionalColumn\CreateAdditionalColumnRuleset;

class CreateAdditionalColumn extends FluxAction
{
    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateAdditionalColumnRuleset::class;
    }

    public function performAction(): AdditionalColumn
    {
        $additionalColumn = app(AdditionalColumn::class, ['attributes' => $this->data]);
        $additionalColumn->save();

        return $additionalColumn->fresh();
    }
}
