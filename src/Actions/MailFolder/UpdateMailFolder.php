<?php

namespace FluxErp\Actions\MailFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailFolder;
use FluxErp\Rulesets\MailFolder\UpdateMailFolderRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateMailFolder extends FluxAction
{
    protected static bool $hasPermission = false;

    protected function getRulesets(): string|array
    {
        return UpdateMailFolderRuleset::class;
    }

    public static function models(): array
    {
        return [MailFolder::class];
    }

    public function performAction(): Model
    {
        $mailFolder = resolve_static(MailFolder::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $mailFolder->fill($this->data);
        $mailFolder->save();

        return $mailFolder->withoutRelations()->fresh();
    }
}
