<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rulesets\AdditionalColumn\DeleteAdditionalColumnRuleset;

class DeleteAdditionalColumn extends FluxAction
{
    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteAdditionalColumnRuleset::class;
    }

    public function performAction(): ?bool
    {
        $additionalColumn = resolve_static(AdditionalColumn::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $additionalColumn->modelValues()->delete();

        return $additionalColumn->delete();
    }
}
