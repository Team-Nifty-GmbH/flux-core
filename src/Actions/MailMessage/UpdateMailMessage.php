<?php

namespace FluxErp\Actions\MailMessage;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateMailMessageRequest;
use FluxErp\Models\MailMessage;

class UpdateMailMessage extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateMailMessageRequest())->rules();
    }

    public static function models(): array
    {
        return [MailMessage::class];
    }

    public function performAction(): mixed
    {
        $mailMessage = MailMessage::query()
            ->whereKey($this->data['id'])
            ->first();

        $mailMessage->fill($this->data);
        $mailMessage->save();

        return $mailMessage->withoutRelations()->fresh();
    }
}
