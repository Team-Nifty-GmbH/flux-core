<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Rulesets\Client\UpdateClientRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateClient extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateClientRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function performAction(): Model
    {
        if ($this->data['is_default'] ?? false) {
            app(Client::class)->query()
                ->whereKeyNot($this->data['id'])
                ->update(['is_default' => false]);
        }

        $bankConnections = Arr::pull($this->data, 'bank_connections');
        $client = app(Client::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $client->fill($this->data);
        $client->save();

        if (! is_null($bankConnections)) {
            $client->bankConnections()->sync($bankConnections);
        }

        return $client->withoutRelations()->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['client_code'] .= ',' . ($this->data['id'] ?? 0);

        if (($this->data['is_default'] ?? false)
            && ! app(Client::class)->query()
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Client::class));

        $this->data = $validator->validate();
    }
}
