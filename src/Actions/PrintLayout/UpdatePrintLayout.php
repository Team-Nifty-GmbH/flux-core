<?php

namespace FluxErp\Actions\PrintLayout;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
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

        $temporaryMedia = $this->getData('temporaryMedia', []);
        // header
        $header = $this->getData('header');

        // first_page_header
        $firstPageHeader = $this->getData('first_page_header');

        // footer
        $footer = $this->getData('footer');
        if($footer['temporaryMedia']) {
            foreach ($footer['temporaryMedia'] as $imagePosition) {
                $index = array_search($imagePosition['name'], array_map(fn ($item) => $item->getFilename(), $temporaryMedia));
                if($index !== false) {
                    // save temporary images to Media
                     $tempMedia = $temporaryMedia[$index];
                     $media =  UploadMedia::make([
                        'media' => $tempMedia,
                        'model_id' => $this->getData('id'),
                        'model_type' => morph_alias(PrintLayout::class),
                        'collection_name' => 'print_layout',
                    ])->checkPermission()
                        ->validate()
                        ->execute();
                     // mutate footer data to match media
                      unset($imagePosition['name']);
                      $imagePosition['id'] = $media->id;
                      $imagePosition['src'] = $media->original_url;
                      // add media to footer
                      $footer['media'][] = $imagePosition;
                } else {
                    throw new \Error('Temporary image not found in temporary media - mismatch between footer and temporary media');
                }

            }
        }
        // remove meta data regarding position of temporary images
        unset($footer['temporaryMedia']);

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
