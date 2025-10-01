<?php

namespace FluxErp\Actions\PrintLayout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintLayout;
use FluxErp\Rulesets\PrintLayout\UpdatePrintLayoutRuleset;

class DeletePrintLayout extends FluxAction
{
    public static function models(): array
    {
        return [PrintLayout::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePrintLayoutRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(PrintLayout::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
