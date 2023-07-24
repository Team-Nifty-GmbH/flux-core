<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Client;

class DeleteClient extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:clients,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function execute(): bool|null
    {
        return Client::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
