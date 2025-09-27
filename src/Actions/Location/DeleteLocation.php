<?php

namespace FluxErp\Actions\Location;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Location;
use FluxErp\Rulesets\Location\DeleteLocationRuleset;

class DeleteLocation extends FluxAction
{
    public static function models(): array
    {
        return [Location::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLocationRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Location::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
