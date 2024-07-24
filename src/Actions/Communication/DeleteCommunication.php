<?php

namespace FluxErp\Actions\Communication;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Communication;
use FluxErp\Rulesets\Communication\DeleteCommunicationRuleset;

class DeleteCommunication extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCommunicationRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Communication::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Communication::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
