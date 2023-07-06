<?php

namespace FluxErp\Actions\Client;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateClientRequest;
use FluxErp\Models\Client;
use Illuminate\Support\Facades\Validator;

class CreateClient implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateClientRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'client.create';
    }

    public static function description(): string|null
    {
        return 'create client';
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function execute(): Client
    {
        $client = new Client($this->data);
        $client->save();

        return $client;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
