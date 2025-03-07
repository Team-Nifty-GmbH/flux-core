<?php

namespace FluxErp\Actions\PrintJob;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintJob;
use FluxErp\Rulesets\PrintJob\UpdatePrintJobRuleset;

class UpdatePrintJob extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdatePrintJobRuleset::class;
    }

    public static function models(): array
    {
        return [PrintJob::class];
    }

    public function performAction(): PrintJob
    {
        $updatePrintJob = resolve_static(PrintJob::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
        $updatePrintJob->fill($this->getData());
        $updatePrintJob->save();

        return $updatePrintJob->withoutRelations()->fresh();
    }
}
