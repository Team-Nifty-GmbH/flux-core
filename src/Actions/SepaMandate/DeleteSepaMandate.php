<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SepaMandate;
use FluxErp\Rulesets\SepaMandate\DeleteSepaMandateRuleset;

class DeleteSepaMandate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteSepaMandateRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [SepaMandate::class];
    }

    public function performAction(): ?bool
    {
        return app(SepaMandate::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
