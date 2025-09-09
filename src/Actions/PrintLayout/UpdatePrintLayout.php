<?php

namespace FluxErp\Actions\PrintLayout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintLayout;
use FluxErp\Rulesets\PrintLayout\UpdatePrintLayoutRuleset;
use FluxErp\Traits\Livewire\PrintLayout\MediaHandler;
use FluxErp\Traits\Livewire\PrintLayout\SnippetHandler;


class UpdatePrintLayout extends FluxAction
{
    use MediaHandler,SnippetHandler;
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

        $temporaryMedia = $this->getData('temporaryMedia', []);

        // header
        $header = $this->getData('header');
        $snapshotDBHeaderMedia = $printLayout->header['media'] ?? [];
        $this->syncMedia($header['media'] ?? [], $snapshotDBHeaderMedia);
        $this->addMedia($header,$temporaryMedia,$this->getData('id'));

        // first_page_header
        $firstPageHeader = $this->getData('first_page_header');
        $snapshotDBFirstPageHeaderMedia = $printLayout->first_page_header['media'] ?? [];
        $this->syncMedia($firstPageHeader['media'] ?? [], $snapshotDBFirstPageHeaderMedia);
        $this->addMedia($firstPageHeader,$temporaryMedia,$this->getData('id'));

        // footer
        $snapshotDBFooterMedia = $printLayout->footer['media'] ?? [];
        $footer = $this->getData('footer');
        $this->syncMedia($footer['media'] ?? [], $snapshotDBFooterMedia);
        $this->addMedia($footer,$temporaryMedia,$this->getData('id'));
        $this->addSnippets($footer,$this->getData('temporary_snippets.footer', []),$this->getData('id'));

        $printLayout->fill([
            'margin' => $this->getData('margin', []),
            'header' => $header,
            'first_page_header' => $firstPageHeader,
            'footer' => $footer,
        ]);

        $printLayout->save();

        return $printLayout->withoutRelations()->fresh();
    }
}
