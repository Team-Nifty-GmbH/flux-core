<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\TicketType;
use Illuminate\Validation\ValidationException;

class DeleteTicketType extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:ticket_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [TicketType::class];
    }

    public function performAction(): ?bool
    {
        return TicketType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

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
    }
}
