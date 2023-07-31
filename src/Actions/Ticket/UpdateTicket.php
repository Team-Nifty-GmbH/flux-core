<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateTicketRequest;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateTicket extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateTicketRequest())->rules();
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function performAction(): Model
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

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Ticket());

        $this->data = $validator->validate();
    }
}
