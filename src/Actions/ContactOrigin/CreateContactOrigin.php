<?php

namespace FluxErp\Actions\ContactOrigin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOrigin;
use FluxErp\Rulesets\ContactOrigin\CreateContactOriginRuleset;

class CreateContactOrigin extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateContactOriginRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ContactOrigin::class];
    }

    public function performAction(): mixed
    {
        $contactOrigin = app(ContactOrigin::class, ['attributes' => $this->data]);
        $contactOrigin->save();

        return $contactOrigin->fresh();
    }
}
