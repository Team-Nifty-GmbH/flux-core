<?php

namespace FluxErp\Actions\PrintLayoutSnippet;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintLayoutSnippet;
use FluxErp\Rulesets\PrintLayoutSnippet\UpdatePrintLayoutSnippetRuleset;

class UpdatePrintLayoutSnippet extends FluxAction
{
    public static function models(): array
    {
        return [PrintLayoutSnippet::class];
    }

    protected function getRulesets(): string|array
    {
        return [UpdatePrintLayoutSnippetRuleset::class];
    }

    public function performAction(): mixed
    {
        $snippet = resolve_static(PrintLayoutSnippet::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $snippet->fill($this->data);
        $snippet->save();

        return $snippet->refresh();
    }
}
