<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOption;

class DeleteContactOption extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:contact_options,id',
        ];
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function performAction(): ?bool
    {
        return ContactOption::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
