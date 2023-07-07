<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateTicketRequest;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateTicket extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateTicketRequest())->rules();
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

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Ticket());

        $this->data = $validator->validate();

        return $this;
    }
}
