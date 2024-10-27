<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rulesets\AdditionalColumn\CreateAdditionalColumnRuleset;

class CreateAdditionalColumn extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return CreateAdditionalColumnRuleset::class;
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): AdditionalColumn
    {
        if (! ($this->data['validations'] ?? false)) {
            $this->data['validations'] = null;
        }

        if (! ($this->data['values'] ?? false)) {
            $this->data['values'] = null;
        }

        $additionalColumn = app(AdditionalColumn::class, ['attributes' => $this->data]);
        $additionalColumn->save();

        return $additionalColumn->fresh();
    }
}
