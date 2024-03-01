<?php

namespace FluxErp\Actions\MailFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailFolder;
use FluxErp\Rulesets\MailFolder\CreateMailFolderRuleset;

class CreateMailFolder extends FluxAction
{
    protected static bool $hasPermission = false;

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateMailFolderRuleset::class, 'getRules');
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
