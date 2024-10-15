<?php

namespace FluxErp\Events\MailAccount;

use FluxErp\Models\MailAccount;
use Illuminate\Queue\SerializesModels;

class Connecting
{
    use SerializesModels;

    public function __construct(public MailAccount $mailAccount) {}
}
