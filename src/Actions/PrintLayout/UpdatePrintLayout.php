<?php

namespace FluxErp\Actions\PrintLayout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintLayout;
use FluxErp\Rulesets\PrintLayout\UpdatePrintLayoutRuleset;


class UpdatePrintLayout extends FluxAction
{
    public static function models(): array
    {
        return [PrintLayout::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePrintLayoutRuleset::class;
    }

    public function performAction(): PrintLayout
    {
        $printLayout = resolve_static(PrintLayout::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();
        $printLayout->fill($this->getData());
        $printLayout->save();

        return $printLayout->withoutRelations()->fresh();
    }
}
