<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Settings\ActivityLogSettings;
use FluxErp\Support\Livewire\Attributes\RenderAs;

class ActivityLogSettingsForm extends SettingsForm
{
    #[RenderAs(RenderAs::NUMBER, options: ['min' => 1, 'step' => 1], label: 'Delete created entries after (days, empty = never)')]
    public ?int $created_retention_days = null;

    #[RenderAs(RenderAs::NUMBER, options: ['min' => 1, 'step' => 1], label: 'Delete updated entries after (days, empty = never)')]
    public ?int $updated_retention_days = 365;

    #[RenderAs(RenderAs::NUMBER, options: ['min' => 1, 'step' => 1], label: 'Delete deleted entries after (days, empty = never)')]
    public ?int $deleted_retention_days = null;

    #[RenderAs(RenderAs::NUMBER, options: ['min' => 1, 'step' => 1], label: 'Delete other entries after (days, empty = never)')]
    public ?int $other_retention_days = 365;

    public function getSettingsClass(): string
    {
        return ActivityLogSettings::class;
    }
}
