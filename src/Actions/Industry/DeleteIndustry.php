<?php

namespace FluxErp\Actions\Industry;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Industry;
use FluxErp\Rulesets\Industry\DeleteIndustryRuleset;

class DeleteIndustry extends FluxAction
{
    public static function models(): array
    {
        return [Industry::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteIndustryRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(Industry::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
