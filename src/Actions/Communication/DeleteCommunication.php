<?php

namespace FluxErp\Actions\Communication;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Communication;
use FluxErp\Rulesets\Communication\DeleteCommunicationRuleset;

class DeleteCommunication extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteCommunicationRuleset::class;
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
