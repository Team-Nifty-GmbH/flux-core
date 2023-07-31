<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateClientRequest;
use FluxErp\Models\Client;

class CreateClient extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateClientRequest())->rules();
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function execute(): Client
    {
        $client = new Client($this->data);
        $client->save();

        return $client->fresh();
    }
}
