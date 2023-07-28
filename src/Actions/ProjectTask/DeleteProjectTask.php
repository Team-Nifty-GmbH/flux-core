<?php

namespace FluxErp\Actions\ProjectTask;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\ProjectTask;

class DeleteProjectTask extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:project_tasks,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [ProjectTask::class];
    }

    public function performAction(): ?bool
    {
        return ProjectTask::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
