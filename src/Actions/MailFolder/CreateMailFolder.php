<?php

namespace FluxErp\Actions\MailFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailFolder;
use FluxErp\Rulesets\MailFolder\CreateMailFolderRuleset;

class CreateMailFolder extends FluxAction
{
    protected static bool $hasPermission = false;

    public static function getRulesets(): string|array
    {
        return CreateMailFolderRuleset::class;
    }

    public static function models(): array
    {
        return [MailFolder::class];
    }

    public function performAction(): MailFolder
    {
        $mailFolder = app(MailFolder::class, ['attributes' => $this->data]);
        $mailFolder->save();

        return $mailFolder->refresh();
    }
}
