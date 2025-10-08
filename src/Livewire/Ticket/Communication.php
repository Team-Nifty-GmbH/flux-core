<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Address;
use FluxErp\Models\Ticket;
use Livewire\Attributes\Renderless;

class Communication extends BaseCommunication
{
    protected ?string $modelType = Ticket::class;

    #[Renderless]
    public function getMailAddress(): string|array|null
    {
        $ticket = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->with('authenticatable')
            ->first([
                'id',
                'authenticatable_type',
                'authenticatable_id',
            ]);

        if ($ticket->authenticatable && $ticket->authenticatable_type === morph_alias(Address::class)) {
            $this->addCommunicatable($ticket->authenticatable->getMorphClass(), $ticket->authenticatable->getKey());

            return $ticket->authenticatable->mail_addresses;
        } else {
            return $ticket->authenticatable?->email;
        }
    }

    #[Renderless]
    public function getPostalAddress(): ?string
    {
        $ticket = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->with('authenticatable')
            ->first([
                'id',
                'authenticatable_type',
                'authenticatable_id',
            ]);

        if ($ticket->authenticatable && $ticket->authenticatable_type === morph_alias(Address::class)) {
            $this->addCommunicatable($ticket->authenticatable->getMorphClass(), $ticket->authenticatable->getKey());

            return implode(
                "\n",
                $ticket->authenticatable->postal_address
            );
        }

        return null;
    }
}
