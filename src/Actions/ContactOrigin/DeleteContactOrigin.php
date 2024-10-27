<?php

namespace FluxErp\Actions\ContactOrigin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOrigin;
use FluxErp\Rulesets\ContactOrigin\DeleteContactOriginRuleset;

class DeleteContactOrigin extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteContactOriginRuleset::class;
    }

    public static function models(): array
    {
        return [ContactOrigin::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(ContactOrigin::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
