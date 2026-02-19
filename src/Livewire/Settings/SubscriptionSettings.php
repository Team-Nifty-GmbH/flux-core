<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\Settings\SubscriptionSettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;
use Illuminate\Contracts\View\View;

class SubscriptionSettings extends SettingsComponent
{
    public SubscriptionSettingsForm $subscriptionSettingsForm;

    public function render(): View
    {
        return view('flux::livewire.settings.subscription-settings');
    }

    protected function getFormPropertyName(): string
    {
        return 'subscriptionSettingsForm';
    }
}
