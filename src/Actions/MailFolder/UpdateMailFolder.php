<?php

namespace FluxErp\Actions\MailFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateMailFolderRequest;
use FluxErp\Models\MailFolder;
use Illuminate\Database\Eloquent\Model;

class UpdateMailFolder extends FluxAction
{
    protected static bool $hasPermission = false;

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateMailFolderRequest())->rules();
    }

    public static function models(): array
    {
        return [MailFolder::class];
    }

    public function performAction(): Model
    {
        $mailFolder = MailFolder::query()
            ->whereKey($this->data['id'])
            ->first();

        $mailFolder->fill($this->data);
        $mailFolder->save();

        return $mailFolder->withoutRelations()->fresh();
    }
}
