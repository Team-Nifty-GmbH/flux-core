<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateTicketTypeRequest;
use FluxErp\Models\TicketType;
use Illuminate\Support\Facades\Validator;

class CreateTicketType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateTicketTypeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'ticket-type.create';
    }

    public static function description(): string|null
    {
        return 'create ticket type';
    }

    public static function models(): array
    {
        return [TicketType::class];
    }

    public function execute(): TicketType
    {
        $ticketType = new TicketType($this->data);
        $ticketType->save();

        return $ticketType;
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
