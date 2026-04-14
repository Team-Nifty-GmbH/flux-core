<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Settings\MailSettings;
use FluxErp\Support\Livewire\Attributes\RenderAs;
use FluxErp\Support\Livewire\Attributes\SeparatorAfter;

class MailSettingsForm extends SettingsForm
{
    public ?string $mailer = null;

    public ?string $host = null;

    public ?int $port = null;

    public ?string $username = null;

    #[RenderAs(type: RenderAs::PASSWORD)]
    #[SeparatorAfter]
    public ?string $password = null;

    public ?string $encryption = null;

    public ?string $from_address = null;

    public ?string $from_name = null;

    public function getSettingsClass(): string
    {
        return MailSettings::class;
    }
}
