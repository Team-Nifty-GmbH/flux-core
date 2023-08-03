<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SepaMandate;

class DeleteSepaMandate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:sepa_mandates,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [SepaMandate::class];
    }

    public function performAction(): ?bool
    {
        return SepaMandate::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
