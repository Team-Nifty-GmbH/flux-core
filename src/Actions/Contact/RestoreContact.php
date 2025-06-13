<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Models\Contact;
use FluxErp\Rulesets\Contact\RestoreContactRuleset;

class RestoreContact extends CreateContact
{
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

        $contact->restore();
        $contact->save();

        return $contact->withoutRelations()->fresh();
    }
}
