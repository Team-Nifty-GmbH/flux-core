<?php

namespace FluxErp\Actions\Printer;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Printer;
use FluxErp\Rulesets\Printer\CreatePrinterRuleset;

class CreatePrinter extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreatePrinterRuleset::class;
    }

    public static function models(): array
    {
        return [Printer::class];
    }

    public function performAction(): Printer
    {
        $printer = app(Printer::class, ['attributes' => $this->getData()]);
        $printer->save();

        return $printer->fresh();
    }
}
