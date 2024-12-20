<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Features\Communications\Communication;
use Livewire\Attributes\Renderless;

class CommunicationList extends Communication
{
    #[Renderless]
    public function getCacheKey(): string
    {
        return parent::getCacheKey() . $this->contact->id;
    }
}
