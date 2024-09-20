<?php

namespace FluxErp\Actions\ContactOrigin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOrigin;
use FluxErp\Rulesets\ContactOrigin\DeleteContactOriginRuleset;

class DeleteContactOrigin extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteContactOriginRuleset::class, 'getRules');
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
