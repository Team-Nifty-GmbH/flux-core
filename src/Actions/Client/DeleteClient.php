<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Rulesets\Client\DeleteClientRuleset;

class DeleteClient extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteClientRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function performAction(): ?bool
    {
        return app(Client::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
