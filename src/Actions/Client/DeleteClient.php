<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Rulesets\Client\DeleteClientRuleset;

class DeleteClient extends FluxAction
{
    public static function models(): array
    {
        return [Client::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteClientRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Client::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
