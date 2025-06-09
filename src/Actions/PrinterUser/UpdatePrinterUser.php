<?php

namespace FluxErp\Actions\PrinterUser;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\PrinterUser;
use FluxErp\Rulesets\PrinterUser\UpdatePrinterUserRuleset;

class UpdatePrinterUser extends FluxAction
{
    public static function models(): array
    {
        return [PrinterUser::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePrinterUserRuleset::class;
    }

    public function performAction(): PrinterUser
    {
        $updatePrinterUser = resolve_static(PrinterUser::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first();
        $updatePrinterUser->fill($this->getData());
        $updatePrinterUser->save();

        return $updatePrinterUser->withoutRelations()->fresh();
    }
}
