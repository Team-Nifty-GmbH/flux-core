<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;

class DeleteContact extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:contacts,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function performAction(): ?bool
    {
        return Contact::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
