<?php

namespace FluxErp\Actions\BlockedFile;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\BlockedFile;
use FluxErp\Rulesets\BlockedFile\DeleteBlockedFileRuleset;

class DeleteBlockedFile extends FluxAction
{
    public static function models(): array
    {
        return [BlockedFile::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteBlockedFileRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(BlockedFile::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
