<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\TicketType\DeleteTicketTypeRuleset;
use Illuminate\Validation\ValidationException;

class DeleteTicketType extends FluxAction
{
    public static function models(): array
    {
        return [TicketType::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteTicketTypeRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(TicketType::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(TicketType::class, 'query')
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
