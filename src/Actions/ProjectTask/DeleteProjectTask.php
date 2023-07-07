<?php

namespace FluxErp\Actions\ProjectTask;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\ProjectTask;

class DeleteProjectTask extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:project_tasks,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [ProjectTask::class];
    }

    public function execute(): bool|null
    {
        return ProjectTask::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
