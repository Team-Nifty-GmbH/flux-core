<?php

namespace FluxErp\Actions\MailAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateMailAccountRequest;
use FluxErp\Models\MailAccount;

class CreateMailAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateMailAccountRequest())->rules();
    }

    public static function models(): array
    {
        return [MailAccount::class];
    }

    public function performAction(): MailAccount
    {
        $mailAccount = new MailAccount($this->data);
        $mailAccount->save();

        return $mailAccount->refresh();
    }
}
