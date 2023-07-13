<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Contact;

class DeleteContact extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:contacts,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function execute(): bool|null
    {
        return Contact::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
