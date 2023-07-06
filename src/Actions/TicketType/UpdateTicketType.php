<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateTicketTypeRequest;
use FluxErp\Models\TicketType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateTicketType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateTicketTypeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'ticket-type.update';
    }

    public static function description(): string|null
    {
        return 'update ticket type';
    }

    public static function models(): array
    {
        return [TicketType::class];
    }

    public function execute(): Model
    {
        $ticketType = TicketType::query()
            ->whereKey($this->data['id'])
            ->first();

        $ticketType->fill($this->data);
        $ticketType->save();

        return $ticketType->withoutRelations()->fresh();
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
