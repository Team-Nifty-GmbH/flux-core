<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateClientRequest;
use FluxErp\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateClient extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateClientRequest())->rules();
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function execute(): Model
    {
        $client = Client::query()
            ->whereKey($this->data['id'])
            ->first();

        $client->fill($this->data);
        $client->save();

        return $client->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Client());

        $this->data = $validator->validate();

        return $this;
    }
}
