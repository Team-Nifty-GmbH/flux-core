<?php

namespace FluxErp\Actions\Presentation;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Presentation;

class DeletePresentation extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:presentations,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Presentation::class];
    }

    public function execute(): bool|null
    {
        return Presentation::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
