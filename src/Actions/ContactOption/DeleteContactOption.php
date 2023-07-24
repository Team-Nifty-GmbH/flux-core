<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\ContactOption;

class DeleteContactOption extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:contact_options,id',
        ];
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function execute(): bool|null
    {
        return ContactOption::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
