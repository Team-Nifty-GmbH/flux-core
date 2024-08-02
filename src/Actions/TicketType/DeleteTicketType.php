<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\TicketType\DeleteTicketTypeRuleset;
use Illuminate\Validation\ValidationException;

class DeleteTicketType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteTicketTypeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [TicketType::class];
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
