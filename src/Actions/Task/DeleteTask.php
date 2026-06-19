<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Contracts\SupportsBulkExecution;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\DeleteTaskRuleset;

class DeleteTask extends DispatchableFluxAction implements SupportsBulkExecution
{
    public static function models(): array
    {
        return [Task::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteTaskRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Task::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
