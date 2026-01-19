<?php

namespace FluxErp\Settings;

class SubscriptionSettings extends FluxSettings
{
    public ?string $cancellation_text = null;

    public int $default_cancellation_notice_value = 0;

    public string $default_cancellation_notice_unit = 'days';

    public static function group(): string
    {
        return 'subscription';
    }
}
