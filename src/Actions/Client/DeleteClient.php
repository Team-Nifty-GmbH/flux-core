<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Client;

class DeleteClient extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:clients,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function performAction(): ?bool
    {
        return Client::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
