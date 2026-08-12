<?php

use FluxErp\Settings\AccountingSettings;
use FluxErp\Settings\CoreSettings;
use FluxErp\Settings\MailSettings;
use FluxErp\Settings\ReminderSettings;
use FluxErp\Settings\SearchSettings;
use FluxErp\Settings\SecuritySettings;
use FluxErp\Settings\SubscriptionSettings;
use FluxErp\Settings\TicketSettings;

return [
    'settings' => [
        AccountingSettings::class,
        CoreSettings::class,
        MailSettings::class,
        ReminderSettings::class,
        SearchSettings::class,
        SecuritySettings::class,
        SubscriptionSettings::class,
        TicketSettings::class,
    ],
];
