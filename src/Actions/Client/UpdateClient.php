<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateClientRequest;
use FluxErp\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateClient extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateClientRequest())->rules();

        $this->rules['client_code'] .= ',' . $this->data['id'];
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function performAction(): Model
    {
        if ($this->data['is_default'] ?? false) {
            Client::query()
                ->whereKeyNot($this->data['id'])
                ->update(['is_default' => false]);
        }

        $bankConnections = Arr::pull($this->data, 'bank_connections');
        $client = Client::query()
            ->whereKey($this->data['id'])
            ->first();

        $client->fill($this->data);
        $client->save();

        if (! is_null($bankConnections)) {
            $client->bankConnections()->sync($bankConnections);
        }

        return $client->withoutRelations()->refresh();
    }

    public function validateData(): void
    {
        if (($this->data['is_default'] ?? false)
            && ! Client::query()
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }

        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Client());

        $this->data = $validator->validate();
    }
}
