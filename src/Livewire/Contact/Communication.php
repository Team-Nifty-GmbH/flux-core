<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Contact;
use Livewire\Attributes\Renderless;

class Communication extends BaseCommunication
{
    protected ?string $modelType = Contact::class;

    #[Renderless]
    public function getCacheKey(): string
    {
        return parent::getCacheKey() . $this->modelType . $this->modelId;
    }
}
