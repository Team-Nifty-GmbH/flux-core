<?php

namespace FluxErp\Actions\PrinterUser;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\PrinterUser;
use FluxErp\Rulesets\PrinterUser\DeletePrinterUserRuleset;

class DeletePrinterUser extends FluxAction
{
    public static function models(): array
    {
        return [PrinterUser::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePrinterUserRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(PrinterUser::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first()
            ->delete();
    }
}
