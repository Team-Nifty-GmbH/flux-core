<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Support\Dashboard as BaseDashboard;
use Livewire\Attributes\Modelable;

class Statistics extends BaseDashboard
{
    #[Modelable]
    public ?int $contactId = null;

    public function getWidgetAttributes(): array
    {
        return [
            'contactId' => $this->contactId,
        ];
    }
}
