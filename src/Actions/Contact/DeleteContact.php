<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Rulesets\Contact\DeleteContactRuleset;

class DeleteContact extends FluxAction
{
    public static function models(): array
    {
        return [Contact::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteContactRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Contact::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
