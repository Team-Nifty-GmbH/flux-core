<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\Settings\TicketSettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;

class TicketSettings extends SettingsComponent
{
    public TicketSettingsForm $ticketSettingsForm;

    protected function getFormPropertyName(): string
    {
        return 'ticketSettingsForm';
    }
}
