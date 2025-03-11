<?php

namespace FluxErp\Actions\Printer;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Printer;
use FluxErp\Rulesets\Printer\UpdatePrinterRuleset;

class UpdatePrinter extends FluxAction
{
    public static function models(): array
    {
        return [Printer::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePrinterRuleset::class;
    }

    public function performAction(): Printer
    {
        $updatePrinter = resolve_static(Printer::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
        $updatePrinter->fill($this->getData());
        $updatePrinter->save();

        return $updatePrinter->withoutRelations()->fresh();
    }
}
