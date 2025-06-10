<?php

namespace FluxErp\Actions\PrinterUser;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\PrinterUser;
use FluxErp\Rulesets\PrinterUser\CreatePrinterUserRuleset;

class CreatePrinterUser extends FluxAction
{
    public static function models(): array
    {
        return [PrinterUser::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePrinterUserRuleset::class;
    }

    public function performAction(): PrinterUser
    {
        $printerUser = app(PrinterUser::class, ['attributes' => $this->getData()]);
        $printerUser->save();

        return $printerUser->fresh();
    }
}
