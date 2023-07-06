<?php

namespace FluxErp\Actions\Client;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Client;
use Illuminate\Support\Facades\Validator;

class DeleteClient implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:clients,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'client.delete';
    }

    public static function description(): string|null
    {
        return 'delete client';
    }

    public static function models(): array
    {
        return [Client::class];
    }

    public function execute(): bool|null
    {
        return Client::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
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
