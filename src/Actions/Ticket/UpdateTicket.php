<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateTicketRequest;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateTicket implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateTicketRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'ticket.update';
    }

    public static function description(): string|null
    {
        return 'update ticket';
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function execute(): Model
    {
        $users = Arr::pull($this->data, 'users');

        $ticket = Ticket::query()
            ->whereKey($this->data['id'])
            ->first();

        $ticket->fill($this->data);
        $ticket->save();

        if (is_array($users)) {
            $ticket->users()->sync($users);
        }

        return $ticket->refresh();
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
