<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Rulesets\Client\CreateClientRuleset;
use Illuminate\Support\Arr;

class CreateClient extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateClientRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function performAction(): Client
    {
        $this->data['is_default'] = ! app(Client::class)->query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            app(Client::class)->query()->update(['is_default' => false]);
        }

        $bankConnections = Arr::pull($this->data, 'bank_connections');

        $client = app(Client::class, ['attributes' => $this->data]);
        $client->save();

        if ($bankConnections) {
            $client->bankConnections()->sync($bankConnections);
        }

        return $client->refresh();
    }
}
