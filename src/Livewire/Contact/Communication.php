<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Contact;

class Communication extends BaseCommunication
{
    protected ?string $modelType = Contact::class;
}
