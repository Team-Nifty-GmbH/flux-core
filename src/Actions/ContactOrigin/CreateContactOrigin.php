<?php

namespace FluxErp\Actions\ContactOrigin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOrigin;
use FluxErp\Rulesets\ContactOrigin\CreateContactOriginRuleset;

class CreateContactOrigin extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateContactOriginRuleset::class;
    }

    public static function models(): array
    {
        return [ContactOrigin::class];
    }

    public function performAction(): ContactOrigin
    {
        $contactOrigin = app(ContactOrigin::class, ['attributes' => $this->data]);
        $contactOrigin->save();

        return $contactOrigin->fresh();
    }
}
