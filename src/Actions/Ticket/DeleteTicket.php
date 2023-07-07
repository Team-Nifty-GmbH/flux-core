<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Ticket;

class DeleteTicket extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:tickets,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function execute(): bool|null
    {
        return Ticket::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
