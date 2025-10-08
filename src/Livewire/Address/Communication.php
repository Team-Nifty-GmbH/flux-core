<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Attributes\Renderless;

class Communication extends BaseCommunication
{
    protected ?string $modelType = Address::class;

    #[Renderless]
    public function getMailAddress(): string|array|null
    {
        return resolve_static($this->modelType, 'query')
            ->whereKey(data_get($this->communication->communicatables, '0.communicatable_id'))
            ->with([
                'contactOptions' => fn (HasMany $query) => $query
                    ->where('type', 'email')
                    ->whereNotNull('value'),
            ])
            ->first([
                'id',
                'email_primary',
            ])
            ?->mail_addresses;
    }

    #[Renderless]
    public function getPostalAddress(): ?string
    {
        return implode(
            "\n",
            resolve_static($this->modelType, 'query')
                ->whereKey(data_get($this->communication->communicatables, '0.communicatable_id'))
                ->first()
                ?->postal_address
        );
    }
}
