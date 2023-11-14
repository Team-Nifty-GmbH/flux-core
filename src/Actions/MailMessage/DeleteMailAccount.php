<?php

namespace FluxErp\Actions\MailMessage;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailMessage;

class DeleteMailAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:mail_messages,id',
        ];
    }

    public static function models(): array
    {
        return [MailMessage::class];
    }

    public function performAction(): mixed
    {
        return MailMessage::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
