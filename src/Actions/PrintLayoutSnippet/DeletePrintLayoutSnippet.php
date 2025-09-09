<?php

namespace FluxErp\Actions\PrintLayoutSnippet;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintLayoutSnippet;
use FluxErp\Rulesets\PrintLayoutSnippet\DeletePrintLayoutSnippetRuleset;

class DeletePrintLayoutSnippet extends FluxAction
{
    public static function models(): array
    {
        return [PrintLayoutSnippet::class];
    }

    protected function getRulesets(): string|array {
        return [DeletePrintLayoutSnippetRuleset::class];
    }

    public function performAction(): mixed
    {
        return resolve_static(PrintLayoutSnippet::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
