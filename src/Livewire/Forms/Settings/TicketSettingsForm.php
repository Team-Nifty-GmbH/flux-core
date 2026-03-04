<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Settings\TicketSettings;
use FluxErp\Support\Livewire\Attributes\RenderAs;

class TicketSettingsForm extends SettingsForm
{
    #[RenderAs(
        RenderAs::SELECT,
        options: [
            'unfiltered' => 'true',
            ':request' => "['url' => route('search', \FluxErp\Models\EmailTemplate::class), 'method' => 'POST', 'params' => ['where' => [['model_type', '=', 'ticket']]]]",
        ],
        label: 'Auto Reply Email Template'
    )]
    public ?int $auto_reply_email_template_id = null;

    public function getSettingsClass(): string
    {
        return TicketSettings::class;
    }
}
