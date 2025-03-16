<?php

namespace FluxErp\Actions\PrintJob;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintJob;
use FluxErp\Rulesets\PrintJob\DeletePrintJobRuleset;

class DeletePrintJob extends FluxAction
{
    public static function models(): array
    {
        return [PrintJob::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePrintJobRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(PrintJob::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
