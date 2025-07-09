<?php

namespace FluxErp\Actions\PrintLayout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintLayout;
use FluxErp\Rulesets\PrintLayout\CreatePrintLayoutRuleset;

class CreatePrintLayout extends FluxAction
{

    public static function models(): array
    {
        return [PrintLayout::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePrintLayoutRuleset::class;
    }

    public function performAction(): PrintLayout
    {
        $printLayout = app(PrintLayout::class, ['attributes' => $this->getData()]);
        $printLayout->save();

        return $printLayout->fresh();
    }
}
