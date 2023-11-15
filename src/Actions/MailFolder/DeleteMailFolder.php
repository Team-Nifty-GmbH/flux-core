<?php

namespace FluxErp\Actions\MailFolder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailFolder;

class DeleteMailFolder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:mail_folders,id',
        ];
    }

    public static function models(): array
    {
        return [MailFolder::class];
    }

    public function performAction(): mixed
    {
        return MailFolder::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
