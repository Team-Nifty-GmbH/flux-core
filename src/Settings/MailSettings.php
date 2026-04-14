<?php

namespace FluxErp\Settings;

class MailSettings extends FluxSettings
{
    public ?string $mailer;

    public ?string $host;

    public ?int $port;

    public ?string $username;

    public ?string $password;

    public ?string $encryption;

    public ?string $from_address;

    public ?string $from_name;

    public static function group(): string
    {
        return 'mail';
    }
}
