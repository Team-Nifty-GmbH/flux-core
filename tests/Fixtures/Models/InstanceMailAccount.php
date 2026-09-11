<?php

namespace FluxErp\Tests\Fixtures\Models;

use FluxErp\Models\MailAccount;

/**
 * Stands in for an instance override (App\Models\MailAccount). A same-basename
 * fixture is impossible under the FluxErp root: the policy guesser's
 * namespace-prefix walk would resolve FluxErp\Policies\MailAccountPolicy and
 * bypass the subclass fallback under test. Real instance classes keep the
 * parent's basename outside the FluxErp prefix, so neither pin below is needed
 * there — table and foreign key only compensate the diverging fixture basename.
 */
class InstanceMailAccount extends MailAccount
{
    protected $table = 'mail_accounts';

    public function getForeignKey(): string
    {
        return 'mail_account_id';
    }
}
