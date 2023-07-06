<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\ToggleTicketUserAssignmentRequest;
use FluxErp\Models\Ticket;
use Illuminate\Support\Facades\Validator;

class ToggleTicketUser implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new ToggleTicketUserAssignmentRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'ticket.toggle-user';
    }

    public static function description(): string|null
    {
        return 'toggle ticket user';
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function execute(): array
    {
        $ticket = Ticket::query()
            ->whereKey($this->data['ticket_id'])
            ->first();

        return $ticket->users()->toggle($this->data['user_id']);
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
