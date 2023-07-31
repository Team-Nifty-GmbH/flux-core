<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Ticket;

class DeleteTicket extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:tickets,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function performAction(): ?bool
    {
        return Ticket::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
