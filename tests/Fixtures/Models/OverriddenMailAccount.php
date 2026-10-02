<?php

namespace FluxErp\Tests\Fixtures\Models;

use FluxErp\Models\MailAccount;

class OverriddenMailAccount extends MailAccount
{
    protected $table = 'mail_accounts';
}
