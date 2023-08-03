<?php

namespace FluxErp\Actions\Presentation;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Presentation;

class DeletePresentation extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:presentations,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Presentation::class];
    }

    public function performAction(): ?bool
    {
        return Presentation::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
