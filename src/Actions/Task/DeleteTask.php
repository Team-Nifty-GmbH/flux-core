<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Task;

class DeleteTask extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:tasks,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Task::class];
    }

    public function performAction(): ?bool
    {
        return Task::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
