<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Rulesets\Client\CreateClientRuleset;
use Illuminate\Support\Arr;

class CreateClient extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateClientRuleset::class;
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function performAction(): Client
    {
        $bankConnections = Arr::pull($this->data, 'bank_connections');

        /** @var Client $client */
        $client = app(Client::class, ['attributes' => $this->data]);
        $client->save();

        if ($bankConnections) {
            $client->bankConnections()->sync($bankConnections);
        }

        return $client->refresh();
    }
}
