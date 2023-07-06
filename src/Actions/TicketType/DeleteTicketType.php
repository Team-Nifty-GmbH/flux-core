<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\TicketType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteTicketType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:ticket_types,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'ticket-type.delete';
    }

    public static function description(): string|null
    {
        return 'delete ticket type';
    }

    public static function models(): array
    {
        return [TicketType::class];
    }

    public function execute(): bool|null
    {
        return TicketType::query()
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

        if (TicketType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->tickets()
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'tickets' => [__('The given ticket type has tickets')],
            ])->errorBag('deleteTicketType');
        }

        return $this;
    }
}
