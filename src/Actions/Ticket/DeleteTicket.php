<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Ticket;
use Illuminate\Support\Facades\Validator;

class DeleteTicket implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:tickets,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'ticket.delete';
    }

    public static function description(): string|null
    {
        return 'delete ticket';
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function execute()
    {
        return Ticket::query()
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
