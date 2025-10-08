<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Contact;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Attributes\Renderless;

class Communication extends BaseCommunication
{
    protected ?string $modelType = Contact::class;

    #[Renderless]
    public function getCacheKey(): string
    {
        return parent::getCacheKey() . $this->modelType . $this->modelId;
    }

    #[Renderless]
    public function getMailAddress(): string|array|null
    {
        $address = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->with([
                'mainAddress' => fn (BelongsTo $query) => $query
                    ->select('id', 'email_primary')
                    ->with([
                        'contactOptions' => fn (HasMany $query) => $query
                            ->where('type', 'email')
                            ->whereNotNull('value'),
                    ]),
            ])
            ->first([
                'id',
                'main_address_id',
            ])
            ?->mainAddress;

        if ($address) {
            $this->addCommunicatable($address->getMorphClass(), $address->getKey());

            return $address->mail_addresses;
        }

        return null;
    }

    #[Renderless]
    public function getPostalAddress(): ?string
    {
        $address = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->with('mainAddress')
            ->first([
                'id',
                'main_address_id',
            ])
            ?->mainAddress;

        if ($address) {
            $this->addCommunicatable($address->getMorphClass(), $address->getKey());

            return implode(
                "\n",
                $address->postal_address
            );
        }

        return null;
    }
}
