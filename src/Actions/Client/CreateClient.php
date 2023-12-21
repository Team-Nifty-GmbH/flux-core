<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateClientRequest;
use FluxErp\Models\Client;
use Illuminate\Support\Arr;

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
        $this->data['is_default'] = ! Client::query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            Client::query()->update(['is_default' => false]);
        }

        $bankConnections = Arr::pull($this->data, 'bank_connections');

        $client = new Client($this->data);
        $client->save();

        if ($bankConnections) {
            $client->bankConnections()->sync($bankConnections);
        }

        return $client->refresh();
    }
}
