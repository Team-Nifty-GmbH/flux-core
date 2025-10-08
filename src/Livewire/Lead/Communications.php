<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Lead;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Attributes\Renderless;

class Communications extends BaseCommunication
{
    protected ?string $modelType = Lead::class;

    #[Renderless]
    public function getMailAddress(): string|array|null
    {
        $address = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->with([
                'address' => fn (BelongsTo $query) => $query
                    ->select('id', 'email_primary')
                    ->with([
                        'contactOptions' => fn (HasMany $query) => $query
                            ->where('type', 'email')
                            ->whereNotNull('value'),
                    ]),
            ])
            ->first([
                'id',
                'address_id',
            ])
            ->address;
        $this->addCommunicatable($address->getMorphClass(), $address->getKey());

        return $address->mail_addresses;
    }

    #[Renderless]
    public function getPostalAddress(): ?string
    {
        $address = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->with('address')
            ->first([
                'id',
                'address_id',
            ])
            ->address;
        $this->addCommunicatable($address->getMorphClass(), $address->getKey());

        return implode(
            "\n",
            $address->postal_address
        );
    }
}
