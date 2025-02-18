<?php

namespace FluxErp\Actions\Industry;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Industry;
use FluxErp\Rulesets\Industry\DeleteIndustryRuleset;

class DeleteIndustry extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteIndustryRuleset::class;
    }

    public static function models(): array
    {
        return [Industry::class];
    }

    public function performAction(): bool
    {
        return resolve_static(Industry::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
