<?php

namespace FluxErp\Actions\MailAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailAccount;

class DeleteMailAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:mail_accounts,id',
        ];
    }

    public static function models(): array
    {
        return [MailAccount::class];
    }

    public function performAction(): ?bool
    {
        return MailAccount::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
