<?php

namespace FluxErp\Actions\MailAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailAccount;
use FluxErp\Rulesets\MailAccount\DeleteMailAccountRuleset;

class DeleteMailAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteMailAccountRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [MailAccount::class];
    }

    public function performAction(): ?bool
    {
        return app(MailAccount::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
