<?php

namespace FluxErp\Settings;

class AccountingSettings extends FluxSettings
{
    public bool $auto_accept_secure_transaction_matches = false;

    public bool $auto_send_reminders = false;

    public static function group(): string
    {
        return 'accounting';
    }
}
