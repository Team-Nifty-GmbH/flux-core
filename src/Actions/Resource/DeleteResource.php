<?php

namespace FluxErp\Actions\Resource;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Resource;
use FluxErp\Rulesets\Resource\DeleteResourceRuleset;

class DeleteResource extends FluxAction
{
    public static function models(): array
    {
        return [Resource::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteResourceRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Resource::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
