<?php

namespace FluxErp\Actions\Industry;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Industry;
use FluxErp\Rulesets\Industry\UpdateIndustryRuleset;

class UpdateIndustry extends FluxAction
{
    public static function models(): array
    {
        return [Industry::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateIndustryRuleset::class;
    }

    public function performAction(): Industry
    {
        $updateIndustry = resolve_static(Industry::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
        $updateIndustry->fill($this->getData());
        $updateIndustry->save();

        return $updateIndustry->withoutRelations()->fresh();
    }
}
