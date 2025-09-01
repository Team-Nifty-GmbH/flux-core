<?php

namespace FluxErp\Actions\PrintLayout;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\PrintLayout;
use FluxErp\Rulesets\PrintLayout\CreatePrintLayoutRuleset;

class CreatePrintLayout extends FluxAction
{

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
        // footer
        $footer = $this->getData('footer', []);
        if($footer['temporaryMedia']) {
            foreach ($footer['temporaryMedia'] as $imagePosition) {
                $index = array_search($imagePosition['name'], array_map(fn ($item) => $item->getFilename(), $temporaryMedia));
                if($index !== false) {
                    // save temporary images to Media
                    $tempMedia = $temporaryMedia[$index];
                    $media =  UploadMedia::make([
                        'media' => $tempMedia,
                        'model_id' => $printLayout->id,
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
            'header' => $this->getData('header', []),
            'first_page_header' => $this->getData('first_page_header', []),
            'footer' => $footer,
        ]);

        $printLayout->save();

        return $printLayout->fresh();
    }
}
