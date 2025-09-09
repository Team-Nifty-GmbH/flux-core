<?php

namespace FluxErp\Actions\PrintLayoutSnippet;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintLayoutSnippet;
use FluxErp\Rulesets\PrintLayoutSnippet\CreatePrintLayoutSnippetRuleset;

class CreatePrintLayoutSnippet extends FluxAction
{

    public static function models(): array
    {
        return  [PrintLayoutSnippet::class];
    }

    protected function getRulesets(): string|array {
        return CreatePrintLayoutSnippetRuleset::class;
    }

    public function performAction(): mixed
    {
        $snippet = app(PrintLayoutSnippet::class, ['attributes' => $this->data]);
        $snippet->save();

        return $snippet->refresh();
    }
}
