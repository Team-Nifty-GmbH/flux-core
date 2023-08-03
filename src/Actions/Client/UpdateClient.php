<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateClientRequest;
use FluxErp\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateClient extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateClientRequest())->rules();
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function performAction(): Model
    {
        $client = Client::query()
            ->whereKey($this->data['id'])
            ->first();

        $client->fill($this->data);
        $client->save();

        return $client->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Client());

        $this->data = $validator->validate();
    }
}
