<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Lead;

class Communications extends BaseCommunication
{
    protected ?string $modelType = Lead::class;
}
