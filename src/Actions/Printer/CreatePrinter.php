<?php

namespace FluxErp\Actions\Printer;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Printer;
use FluxErp\Rulesets\Printer\CreatePrinterRuleset;

class CreatePrinter extends FluxAction
{
    public static function models(): array
    {
        return [Printer::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePrinterRuleset::class;
    }

    public function performAction(): Printer
    {
        $printer = app(Printer::class, ['attributes' => $this->getData()]);
        $printer->save();

        return $printer->fresh();
    }
}
