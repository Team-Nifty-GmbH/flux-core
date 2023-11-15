<?php

namespace FluxErp\Actions\MailFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateMailFolderRequest;
use FluxErp\Models\MailFolder;

class CreateMailFolder extends FluxAction
{
    protected static bool $hasPermission = false;

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateMailFolderRequest())->rules();
    }

    public static function models(): array
    {
        return [MailFolder::class];
    }

    public function performAction(): mixed
    {
        $mailFolder = new MailFolder($this->data);
        $mailFolder->save();

        return $mailFolder->withoutRelations()->refresh();
    }
}
