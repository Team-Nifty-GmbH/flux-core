<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateClientRequest;
use FluxErp\Models\Client;

class CreateClient extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateClientRequest())->rules();
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function performAction(): Client
    {
        $client = new Client($this->data);
        $client->save();

        return $client;
    }
}
