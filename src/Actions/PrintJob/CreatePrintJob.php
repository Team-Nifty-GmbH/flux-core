<?php

namespace FluxErp\Actions\PrintJob;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintJob;
use FluxErp\Rulesets\PrintJob\CreatePrintJobRuleset;

class CreatePrintJob extends FluxAction
{
    public static function models(): array
    {
        return [PrintJob::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePrintJobRuleset::class;
    }

    public function performAction(): PrintJob
    {
        $printJob = app(PrintJob::class, ['attributes' => $this->getData()]);
        $printJob->save();

        return $printJob->fresh();
    }

    public function prepareForValidation(): void
    {
        $this->data['user_id'] ??= auth()->id();
        $this->data['quantity'] ??= 1;
    }
}
