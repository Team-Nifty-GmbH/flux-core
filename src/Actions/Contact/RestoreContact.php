<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Rulesets\Contact\RestoreContactRuleset;

class RestoreContact extends FluxAction
{
    public static function models(): array
    {
        return [Contact::class];
    }

    protected function getRulesets(): string|array
    {
        return RestoreContactRuleset::class;
    }

    public function performAction(): Contact
    {
        /** @var Contact $contact */
        $contact = resolve_static(Contact::class, 'query')
            ->onlyTrashed()
            ->whereKey($this->getData('id'))
            ->first();
        $contact->fill($this->getData());

        $contact->restore();

        return $contact->withoutRelations()->fresh();
    }
}
