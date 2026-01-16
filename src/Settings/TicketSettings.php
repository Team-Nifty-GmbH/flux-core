<?php

namespace FluxErp\Settings;

class TicketSettings extends FluxSettings
{
    public ?int $auto_reply_email_template_id = null;

    public static function group(): string
    {
        return 'ticket';
    }
}
