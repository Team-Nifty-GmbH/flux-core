<?php

namespace FluxErp\Actions\PrintLayout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintLayout;
use FluxErp\Rulesets\PrintLayout\CreatePrintLayoutRuleset;
use FluxErp\Traits\Livewire\PrintLayout\MediaHandler;
use FluxErp\Traits\Livewire\PrintLayout\SnippetHandler;

class CreatePrintLayout extends FluxAction
{
    use MediaHandler, SnippetHandler;
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
        $temporaryMedia = $this->getData('temporaryMedia', []);

        // save print layout to obtain its ID for media relation
        $printLayout = app(PrintLayout::class);
        $printLayout->fill([
            'client_id' => $this->getData('client_id'),
            'name' => $this->getData('name'),
            'model_type' => $this->getData('model_type'),
        ]);
        $printLayout->save();
        // header
        $header = $this->getData('header', []);
        $this->addMedia($header,$temporaryMedia,$printLayout->id);

        // first page header
        $firstPageHeader = $this->getData('first_page_header', []);
        $this->addMedia($firstPageHeader,$temporaryMedia,$printLayout->id);

        // footer
        $footer = $this->getData('footer', []);
        $this->addMedia($footer,$temporaryMedia,$printLayout->id);
        $this->addSnippets($footer,$this->getData('temporary_snippets.footer', []),$printLayout->id);


        $printLayout->fill([
            'margin' => $this->getData('margin', []),
            'header' => $header,
            'first_page_header' => $firstPageHeader,
            'footer' => $footer,
        ]);

        $printLayout->save();

        return $printLayout->fresh();
    }
}
