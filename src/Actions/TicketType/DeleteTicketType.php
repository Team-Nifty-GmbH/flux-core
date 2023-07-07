<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\TicketType;
use Illuminate\Validation\ValidationException;

class DeleteTicketType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:ticket_types,id,deleted_at,NULL',
        ];
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

    public function validate(): static
    {
        parent::validate();

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
