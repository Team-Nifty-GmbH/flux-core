<?php

namespace FluxErp\Actions\Printer;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Printer;
use FluxErp\Rulesets\Printer\DeletePrinterRuleset;

class DeletePrinter extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeletePrinterRuleset::class;
    }

    public static function models(): array
    {
        return [Printer::class];
    }

    public function performAction(): bool
    {
        return resolve_static(Printer::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
