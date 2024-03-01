<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Ticket;
use FluxErp\Rulesets\Ticket\DeleteTicketRuleset;

class DeleteTicket extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteTicketRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function performAction(): ?bool
    {
        return app(Ticket::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
