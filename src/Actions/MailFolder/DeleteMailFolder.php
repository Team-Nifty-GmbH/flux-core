<?php

namespace FluxErp\Actions\MailFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailFolder;
use FluxErp\Rulesets\MailFolder\DeleteMailFolderRuleset;

class DeleteMailFolder extends FluxAction
{
    protected static bool $hasPermission = false;

    protected function getRulesets(): string|array
    {
        return DeleteMailFolderRuleset::class;
    }

    public static function models(): array
    {
        return [MailFolder::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(MailFolder::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
