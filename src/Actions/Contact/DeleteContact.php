<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Rulesets\Contact\DeleteContactRuleset;

class DeleteContact extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteContactRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function performAction(): ?bool
    {
        return app(Contact::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
